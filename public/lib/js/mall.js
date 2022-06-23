var dataUrl={
    'prov':'https://unpkg.com/province-city-china@8.1.0/dist/province.min.json',
    'city':'https://unpkg.com/province-city-china@8.1.0/dist/city.min.json',
    'area':'https://unpkg.com/province-city-china@8.1.0/dist/area.min.json',
    'level':'https://unpkg.com/province-city-china@8.1.0/dist/level.json',
}
var areaData;
function areaFormat(data){
    let s=[];
    for (let v in data){
        s.push('<option value="'+v+'">'+data[v].name+'</option>')
    }
    return s.join("");
}