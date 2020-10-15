<div>
    <div class="form-group">
        <select name="np_pickup_city" id="np-pickup-city" [+np-pickup-city.class+]>
            <option value="">[%select_city%]</option>
            [[if? &is=`[+city.id+]:!empty` &then=`
                <option value="[+city.id+]" selected>[+city.text+]</option>
            `]]
        </select>
        [+np-pickup-city.error+]
    </div>
    <div class="form-group">
        <select name="np_pickup_department" id="np-pickup-department" [+np-pickup-department.class+]>
            <option value="">[%select_department%]</option>
            [[if? &is=`[+department.id+]:!empty` &then=`
            <option value="[+department.id+]" selected>[+department.text+]</option>
            `]]
        </select>
        [+np-pickup-department.error+]
    </div>
</div>
