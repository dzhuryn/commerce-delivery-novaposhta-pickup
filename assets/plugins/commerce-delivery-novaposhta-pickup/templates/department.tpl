<select name="order[fields][np_pickup_department]" id="np-pickup-department" >
    <option value="">[%select_department%]</option>
    [[if? &is=`[+department.id+]:!empty` &then=`
    <option value="[+department.id+]" selected>[+department.text+]</option>
    `]]
</select>