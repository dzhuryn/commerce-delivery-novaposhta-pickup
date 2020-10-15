<select name="order[fields][np_pickup_city]" id="np-pickup-city">
    <option value="">[%select_city%]</option>
    [[if? &is=`[+city.id+]:!empty` &then=`
    <option value="[+city.id+]" selected>[+city.text+]</option>
    `]]
</select>
