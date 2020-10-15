<?php
require MODX_BASE_PATH.'assets/plugins/commerce-delivery-novaposhta-pickup/autoload.php';
if (empty($modx->commerce) && !defined('COMMERCE_INITIALIZED')) {
    return;
}
/** @var \Commerce\Commerce $commerce */
$commerce = $modx->commerce;

$assets = AssetsHelper::getInstance($modx);
$config = new \Helpers\Config($params);
$render = DLTemplate::getInstance($modx);

$lexicon =  new \Helpers\Lexicon($modx,[
    'lang' => $commerce->getCurrentLang(),
    'langDir'=>'assets/plugins/commerce-delivery-novaposhta-pickup/lang/'
]);
$lexicon->fromFile('core');

$langCode = $lexicon->get('lang_code');
$lexiconCode = $lexicon->get('lexicon_code');


switch ($modx->event->name) {
    case 'OnPageNotFound':
        if($_GET['q'] !=='commerce-delivery-np-pickup'){
            return true;
        }
        $action = $_GET['action'];
        $controller = new \CommerceDeliveryNpPickup\Controller($modx,$config);
        if(method_exists($controller,$action)){
            $response = call_user_func_array([$controller,$action],[]);
        }
        header('Content-type: text/json');
        echo json_encode($response,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        die();
        break;
    case 'OnRegisterDelivery':
        /** @var \Commerce\Processors\OrdersProcessor $processor */
        $processor = $modx->commerce->loadProcessor();


       $scripts = '';
        if($config->getCFGDef('loadSelect2')) {
            $scripts .= $assets->registerScriptsList([
                'select2.css' => ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/css/select2.min.css'],
                'select2.js'=> ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/js/select2.min.js'],
                'select2.lang'=> ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/js/i18n/' . $lexiconCode . '.js'],
            ]);
        }
        if($config->getCFGDef('loadCSS')){
            $scripts .= $assets->registerScript('novaposhta-pickup.css',['src'=>'assets/plugins/commerce-delivery-novaposhta-pickup/js/novaposhta-pickup.css']);
        }
        if($config->getCFGDef('loadJS')){
            $jsConfig = [
                'langCode'=>$lexicon->get('lang_code'),
                'lexiconCode'=>$lexicon->get('lexicon_code'),
            ];
            $scripts .= '<script> var deliveryNpConfig = '.json_encode($jsConfig,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</script>';
            $scripts .= $assets->registerScript('novaposhta-pickup.js',['src'=>'assets/plugins/commerce-delivery-novaposhta-pickup/js/novaposhta-pickup.js']);
        }
        $modx->regClientHTMLBlock($scripts);
        $template = $config->getCFGDef('template','@CODE:'.file_get_contents(MODX_BASE_PATH.'assets/plugins/commerce-delivery-novaposhta-pickup/templates/markup.tpl'));

        $markupData = [];

        if(!empty($_REQUEST['np_pickup_city'])){
            $cityRef = $_REQUEST['np_pickup_city'];
            $city = new \CommerceDeliveryNpPickup\Model\City($modx);
            $field = $lexicon->get('lang_code') === 'ua'?'city':'city_'.$lexicon->get('lang_code');
            $markupData['city'] = $city->getCityByRef($cityRef,$field);
        }

        if(!empty($_REQUEST['np_pickup_department'])){
            $departmentRef = $_REQUEST['np_pickup_department'];
            $city = new \CommerceDeliveryNpPickup\Model\Department($modx);
            $field = $lexicon->get('lang_code') === 'ua'?'address':'address_'.$lexicon->get('lang_code');
            $markupData['department'] = $city->getDepartmentByRef($departmentRef,$field);

        }
        $markup = $render->parseChunk($template,$markupData);
        $markup= $lexicon->parse($markup);

        if(!($processor->getCurrentDelivery() === 'novaposhta-pickup' || empty($params['rows']))){
            $markup = '';
        }

        // Регистрация доставки
        $params['rows']['novaposhta-pickup'] = [
            'title' => $config->getCFGDef('title',$lexicon->get('title')),
            'markup' => $markup,
            'price' => 0,
        ];
        break;
    case 'OnManagerBeforeOrderRender':
        if($order['fields']['delivery_method'] === 'novaposhta-pickup' && isset($params['groups']['payment_delivery']['fields'])){
            $params['groups']['payment_delivery']['fields']['np_pickup_city'] = [
                'title' => $lexicon->get('delivery_city'),
                'content' => function($data) use($order,$lexicon,$modx) {
                    $cityModel = new \CommerceDeliveryNpPickup\Model\City($modx);
                    $field = $lexicon->get('lang_code') === 'ua'?'city':'city_'.$lexicon->get('lang_code');
                    $city = $cityModel->getCityByRef($order['fields']['np_pickup_city'],$field);

                    return !empty($city) ? $city['text'] : '';
                },
                'sort' => 21,
            ];
            $params['groups']['payment_delivery']['fields']['np_pickup_department'] = [
                'title' => $lexicon->get('department'),
                'content' => function($data) use($order,$modx,$lexicon) {
                    $departmentModel = new \CommerceDeliveryNpPickup\Model\Department($modx);
                    $field = $lexicon->get('lang_code') === 'ua'?'address':'address_'.$lexicon->get('lang_code');
                    $department = $departmentModel->getDepartmentByRef($order['fields']['np_pickup_department'],$field);

                    return !empty($department) ? $department['text'] : '';
                },
                'sort' => 22,
            ];
        }

        break;
    case 'OnManagerBeforeOrderEditRender':

        $params['fields']['np_pickup_city'] = [
            'title' => $lexicon->get('delivery_city'),
            'content' => function ($data) use ($order, $modx, $render, $lexicon) {

                $cityModel = new \CommerceDeliveryNpPickup\Model\City($modx);
                $field = $lexicon->get('lang_code') === 'ua'?'city':'city_'.$lexicon->get('lang_code');
                $city = $cityModel->getCityByRef($order['fields']['np_pickup_city'],$field);
                $template = '@CODE:' . file_get_contents(MODX_BASE_PATH . 'assets/plugins/commerce-delivery-novaposhta-pickup/templates/city.tpl');

                return $lexicon->parse(
                    $render->parseChunk($template, [
                        'city' => $city
                    ])
                );
            },
            'sort' => 41,
        ];

        $params['fields']['np_pickup_department'] = [
            'title' => $lexicon->get('department'),
            'content' => function ($data) use ($order, $modx, $render, $lexicon) {

                $departmentModel = new \CommerceDeliveryNpPickup\Model\Department($modx);
                $field = $lexicon->get('lang_code') === 'ua'?'address':'address_'.$lexicon->get('lang_code');
                $department = $departmentModel->getDepartmentByRef($order['fields']['np_pickup_department'],$field);
                $template = '@CODE:' . file_get_contents(MODX_BASE_PATH . 'assets/plugins/commerce-delivery-novaposhta-pickup/templates/department.tpl');

                return $lexicon->parse(
                    $render->parseChunk($template, [
                        'department' => $department
                    ])
                );
            },
            'sort' => 41,
        ];

        break;
    case 'OnManagerOrderEditRender':
        $scripts = '';
        $scripts .= $assets->registerScriptsList([
            'manager.select2.css' => ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/css/select2.min.css'],
            'manager.select2.js'=> ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/js/select2.min.js'],
            'manager.select2.lang'=> ['src' => 'assets/plugins/commerce-delivery-novaposhta-pickup/js/i18n/' . $lexiconCode . '.js'],
        ]);

        $jsConfig = [
            'langCode'=>$lexicon->get('lang_code'),
            'lexiconCode'=>$lexicon->get('lexicon_code'),
        ];
        $scripts .= '<script> var deliveryNpConfig = '.json_encode($jsConfig,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT).'</script>';
        $scripts .= $assets->registerScript('manager.novaposhta-pickup.js',['src'=>'assets/plugins/commerce-delivery-novaposhta-pickup/js/novaposhta-pickup.js']);
        $scripts .= $assets->registerScript('manager.commerce.js',['src'=>'assets/plugins/commerce-delivery-novaposhta-pickup/js/commerce.js']);

        $modx->event->addOutput($scripts);
        break;
}