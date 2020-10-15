<?php
namespace CommerceDeliveryNpPickup\Model;

class Department
{
    private $table = 'np_departments';
    /**
     * @var \DocumentParser
     */
    private $modx;

    public function __construct(\DocumentParser $modx)
    {
        $this->modx = $modx;
        $this->table = $modx->getFullTableName($this->table);
    }

    private function createOrUpdate($fields){
        $fields['update_status'] = 1;
        $eFields = $this->modx->db->escape($fields);

        $id = $this->modx->db->getValue($this->modx->db->select('id',$this->table,"`ref` = '".$eFields['ref']."'"));

        if($id){
            $this->modx->db->update($eFields,$this->table,"`ref` = '".$eFields['ref']."'");
        }
        else{
            $this->modx->db->insert($eFields,$this->table);
        }
    }

    public function update($departments)
    {
        $this->modx->db->update([
            'update_status'=>0
        ],$this->table);

        foreach ($departments as $department) {
            $this->createOrUpdate($department);
        }
        //удаляем старие города в которых уже нет отделений
        $this->modx->db->delete($this->table,'update_status = 0');
    }

    public function getDepartmentsByCityRef($cityRef, $field)
    {
        $eField = $this->modx->db->escape($field);
        $eCityRef = $this->modx->db->escape($cityRef);

        return $this->modx->db->makeArray(
            $this->modx->db->select("`ref` as id, $eField as `text`",$this->table,"`city_ref` = '$eCityRef'","num asc")
        );
    }


    public function getDepartmentByRef($departmentRef, $field = 'address')
    {
        $eRef = $this->modx->db->escape($departmentRef);
        $eField = $this->modx->db->escape($field);


        return $this->modx->db->getRow(
            $this->modx->db->select("ref as id, $eField as text",$this->table,"`ref` = '$eRef'")
        );
    }

    public function search($cityRef, $query, $field)
    {
        $eSearch = $this->modx->db->escape($query);
        $eField = $this->modx->db->escape($field);
        $eCityRef = $this->modx->db->escape($cityRef);

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

            $where[] = "(`$eField` like '$eSearch' or `$eField` like '$eSearch%' or `$eField` like '%$eSearch%')";

            $order = array_merge($order, [
                "search desc", "lLike desc",
                "fullLike desc "
            ]);
        }
        if(!empty($eCityRef)){
            $where[] = "`city_ref` = '$eCityRef'";
        }



        $order[] = "`num` asc";

        $sql = "select ".implode(',',$select)." from $this->table ";
        if(!empty($where)){
            $sql .= "where ".implode(' and ',$where)." ";
        }
        if(!empty($order)){
            $sql .= "order by ".implode(', ',$order)." ";
        }
        $sql .= "limit 40";


        return $this->modx->db->makeArray($this->modx->db->query($sql));
    }
}