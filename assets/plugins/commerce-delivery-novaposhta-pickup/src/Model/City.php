<?php

namespace CommerceDeliveryNpPickup\Model;

class City
{
    private $table = 'np_cities';
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct(\DocumentParser $modx)
    {
        $this->table = $modx->getFullTableName($this->table);
        $this->modx = $modx;
    }


    private function createOrUpdate($fields)
    {
        $fields['update_status'] = 1;
        $eFields = $this->modx->db->escape($fields);

        $id = $this->modx->db->getValue($this->modx->db->select('id', $this->table, "`ref` = '" . $eFields['ref'] . "'"));

        if ($id) {
            $this->modx->db->update($eFields, $this->table, "`ref` = '" . $eFields['ref'] . "'");
        } else {
            $this->modx->db->insert($eFields, $this->table);
        }
    }

    public function update($cities)
    {
        $this->modx->db->update([
            'update_status' => 0
        ], $this->table);

        foreach ($cities as $city) {
            $this->createOrUpdate($city);
        }
        //удаляем старие города в которых уже нет отделений
        $this->modx->db->delete($this->table, 'update_status = 0');
    }

    public function search($query, $field)
    {

        $eSearch = $this->modx->db->escape($query);
        $eField = $this->modx->db->escape($field);


        $select = [
            "`ref` as `id`",
            "`$eField` as `text`",
        ];
        $where = [];
        $order = [];

        if (!empty($eSearch)) {
            $select = array_merge($select,[
                "(`$eField` like '$eSearch') as search",
                "(`$eField` like '$eSearch%') as lLike",
                "(`$eField` like '%$eSearch%') as fullLike"
            ], $select);

            $where = array_merge($where, [
                "`$eField` like '$eSearch'",
                "`$eField` like '$eSearch%'",
                "`$eField` like '%$eSearch%'"
            ]);

            $order = array_merge($order, [
                "search desc", "lLike desc",
                "fullLike desc, city "
            ]);
        }

        $order[] = "$eField COLLATE  utf8_unicode_ci";

        $sql = "select ".implode(',',$select)." from $this->table ";
        if(!empty($where)){
            $sql .= "where ".implode(' or ',$where)." ";
        }
        if(!empty($order)){
            $sql .= "order by ".implode(', ',$order)." ";
        }
        $sql .= "limit 40";

        return $this->modx->db->makeArray($this->modx->db->query($sql));
    }

    public function getCityByRef($ref,$field = 'city')
    {

        $eRef = $this->modx->db->escape($ref);
        $eField = $this->modx->db->escape($field);

        return $this->modx->db->getRow(
            $this->modx->db->select("ref as id, $eField as text",$this->table,"`ref` = '$eRef'")
        );
    }
}