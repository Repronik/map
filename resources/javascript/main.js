var cookies = {};
var aproved = '2';
var adding = false;
var isAdmin = false;

tempCookies = decodeURIComponent(document.cookie).split(';');
for (var i = tempCookies.length - 1; i >= 0; i--) {
    [key, value] = tempCookies[i].trim().split('=', 2);
    cookies[key] = value;
}

if (cookies.isAdmin == '1') {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            setAdmin((this.responseText == '1'))
        }
    };
    xhttp.open("GET", "testAdmin.php", true);
    xhttp.send();
}


styleCache = {};
var clusters = new ol.layer.Vector({
    style: function(feature) {

        radius = 12;
        scale = 1;
        if (isMobile()) {
            radius = 45;
            scale = 5;
        }

        size = feature.get('features').length;
        style = styleCache[size];
        if (!style) {
            style = new ol.style.Style({
                image: new ol.style.Circle({
                    radius: radius,
                    stroke: new ol.style.Stroke({
                        color: '#fff'
                    }),
                    fill: new ol.style.Fill({
                        color: '#3399CC'
                    })
                }),
                text: new ol.style.Text({
                    scale: scale,
                    text: size.toString(),
                    fill: new ol.style.Fill({
                        color: '#fff'
                    })
                })
            });
            styleCache[size] = style;
        }
        return style;
    }
})

var view = new ol.View({
    center: [0, 20],
    zoom: 3
});

var addMarkerOverlay = new ol.Overlay({
    // element: document.getElementById('addMarker'),
    autoPan: true,
    autoPanAnimation: {
        duration: 250
    }
});

var MarkerOverlay = new ol.Overlay({
    // element: document.getElementById('Marker'),
    autoPan: true,
    autoPanAnimation: {
        duration: 250
    }
});

var openSteetMapLayer = new ol.layer.Tile({
    source: new ol.source.OSM({
        attributions: ['for suport or sugestions mail cuteboyworld@outlook.com'],
        wrapX: false
    }),
});

var controls = ol.control.defaults({rotate: false}); 
var interactions = ol.interaction.defaults({altShiftDragRotate:false, pinchRotate:false});

var map = new ol.Map({
    layers: [ openSteetMapLayer, clusters ],
    overlays: [addMarkerOverlay, MarkerOverlay],
    view: view,
    controls: controls,
    interactions: interactions
});


map.on('singleclick', function(evt) {
    feat = map.getFeaturesAtPixel(evt.pixel);
    if (feat && feat.length == 1 && feat[0].get('features').length == 1) {
        coordinates = feat[0].getGeometry().getCoordinates();
        id = feat[0].get('features')[0].get('id');
        view = map.getView();
        view.setCenter(coordinates);
        view.setZoom(10);
        showMarker(id, coordinates);
    } else if (adding && evt.coordinate[0] > -20037508 && evt.coordinate[0] < 20037508 && evt.coordinate[1] > -20037508 && evt.coordinate[1] < 20037508) {
        addMarkerOverlay.setPosition(evt.coordinate);
        if (isMobile()) {
            document.getElementById('addMarker').style.display = 'block';
        }
    } else {
        MarkerOverlay.setPosition(undefined);
        addMarkerOverlay.setPosition(undefined);
        if (isMobile()) {
            document.getElementById('addMarker').style.display = 'none';
            document.getElementById('Marker').style.display = 'block'
        }
    }
});

function showImg() {
    document.getElementById("nsfwOverlay").style.display = 'none';
    document.getElementById("markerPhoto").style.display = 'block';
}
function showNSFW(input) {
    if (input.value == '') {
        document.getElementById("addMarkerNsfwRow").style.display = 'none';
    } else {
        document.getElementById("addMarkerNsfwRow").style.display = 'table-row';
    }
}

function setAdmin(bool) {
    isAdmin = bool;
    if (isAdmin) {
        document.getElementById('adminCommands').style.display = 'block';
        document.getElementById('add-marker').style.display = 'none';
        document.getElementById('judgeButtons').style.display = 'table-row';
    }
}

function toggleAddMarker(button) {
    adding = !adding;
    if (adding) {
        button.value = "Cancel adding marker";
    } else {
        button.value = "Add marker";
        addMarkerOverlay.setPosition(undefined);
        if (isMobile()) {
            document.getElementById('addMarker').style.display = 'none';
        }
    }
}

function adminMarkers(button) {
    buttons = document.getElementsByClassName("adminButton");
    for (var i = buttons.length - 1; i >= 0; i--) {
        buttons[i].disabled = false;
    }
    button.disabled = true;
    aproved = button.getAttribute("command");
    loadMarkers("?aproved="+aproved);
}




function showMarker(id, pos) {
    document.getElementById("addMarkerNsfwRow").style.display = 'none';
    document.getElementById('markerPhoto').src = '';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            response = JSON.parse(this.responseText);
            if (response['photo']) {
                if (response['nsfw'] == '1') {
                    document.getElementById("markerPhoto").style.display = 'none';
                    document.getElementById("nsfwOverlay").style.display = 'block';
                }
                document.getElementById('markerPhoto').src = '/images/'+response['photo'];
            }
            document.getElementById('markerName').innerHTML = response['name'];
            document.getElementById('markerEmail').innerHTML = response['email'];
            document.getElementById('markerText').innerHTML = response['text'];
            MarkerOverlay.setPosition(pos);
            MarkerOverlay.id = id;
            if (isMobile()) {
                document.getElementById('Marker').style.display = 'block'
            }
        }
    };
    xhttp.open("GET", "getMarkers.php?id="+id, true);
    xhttp.send();
}

function loadMarkers(get) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            response = JSON.parse(this.responseText);

            features = new Array(response.length);
            for (var i = response.length - 1; i >= 0; i--) {
                // var coordinates = [2 * e * Math.random() - e, 2 * e * Math.random() - e];
                // console.log(response[i]['pos'], ol.proj.transform(response[i]['pos'], 'EPSG:4326', 'EPSG:3857'));
                features[i] = new ol.Feature({
                    geometry: new ol.geom.Point(response[i]['pos']),
                    // name: response[i]['id']
                    id: response[i]['id']
                });
            }

            source = new ol.source.Vector({
                features: features
            });

            distance = 30;
            if (isMobile()) {
                distance = 100;
            }

            clusterSource = new ol.source.Cluster({
                wrapX: false,
                distance: distance,
                source: source
            });

            clusters.setSource(clusterSource);
        }
    };
    if (get) {
        xhttp.open("GET", "getMarkers.php"+get, true);
    } else {
        xhttp.open("GET", "getMarkers.php", true);
    }
    xhttp.send();

}


function isMobile() {
    var check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|android|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
}

function procesMarker(button) {
    if (isAdmin) {
        if (button.id == 'edit') {

            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    response = JSON.parse(this.responseText);

                    if (response['photo']) {
                        document.getElementById("addMarkerNsfwRow").style.display = 'table-row';
                        // document.getElementById('markerPhoto').src = '/images/'+response['photo'];
                    } else {
                        document.getElementById("addMarkerNsfwRow").style.display = 'none';
                        // document.getElementById('markerPhoto').src = '';
                    }

                    if (response['nsfw'] == '1') {
                        document.getElementById("addMarkerNsfw").checked = true;
                    } else {
                        document.getElementById("addMarkerNsfw").checked = false;
                    }

                    document.getElementById("addMarkerFileRow").style.display = 'none';
                    document.getElementById('addMarkerName').value = response['name'];
                    document.getElementById('addMarkerEmail').value = response['email'];
                    document.getElementById('addMarkerText').value = response['text'].replace(/<br\s*\/?>/mg,"");
                    document.getElementById('addMarkerEdit').value = MarkerOverlay.id;
                    captchaRow = document.getElementById('addMarkerCaptcha');
                    captchaRow.style.display = 'none';
                    captchaRow.children[1].children[0].required = false;
                }
            };
            xhttp.open("GET", "getMarkers.php?id="+MarkerOverlay.id, true);
            xhttp.send();


            addMarkerOverlay.setPosition(MarkerOverlay.getPosition());
            MarkerOverlay.setPosition(undefined);
        } else {
            MarkerOverlay.setPosition(undefined);
            // MarkerCloser.blur();

            if (button.id == 'accept') {
                loadMarkers("?aproved="+aproved+"&accept="+MarkerOverlay.id);
            } else {
                loadMarkers("?aproved="+aproved+"&reject="+MarkerOverlay.id);
            }
        }
    }
}

function checkForm() {
    errorElements = document.getElementsByClassName('form-error');
    for (var i = errorElements.length - 1; i >= 0; i--) {
        // console.log(errorElements[i]);
        errorElements[i].parentElement.removeChild(errorElements[i]);
    }

    form = document.forms['addMarker-form'];
    error = false;

    if (!(form['edit'].value > '' && isAdmin)) {

        if (form['file'].files.length > 0 ) {
            if (form['file'].files[0].size > 2097152) {
                td = form['file'].parentElement;
                strong = document.createElement('strong');
                strong.className = 'form-error';
                strong.innerHTML = 'File is too big Max 2Mib';
                td.appendChild(strong);
                td.appendChild(document.createElement('br'));
                error = true;
            }
            if (!(['image/jpeg', 'image/png'].includes(form['file'].files[0].type))) {
                alert(form['file'].files[0].type);
                td = form['file'].parentElement;
                strong = document.createElement('strong');
                strong.className = 'form-error';
                strong.innerHTML = 'Only images allowd (jpeg or png)';
                td.appendChild(strong);
                error = true;
            }
        }
        if (form['captcha'].value == '') {
            td = form['captcha'].parentElement;
            strong = document.createElement('strong');
            strong.className = 'form-error';
            strong.innerHTML = 'Cant be empty';
            td.appendChild(strong);
            error = true;
        }

    }

    if (form['name'].value == '') {
        td = form['name'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Cant be empty';
        td.appendChild(strong);
        error = true;
    } else if (form['name'].value.length > 64) {
        td = form['name'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Thats Too long';
        td.appendChild(strong);
        error = true;
    }
    if (form['email'].value == '') {
        td = form['email'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Cant be empty';
        td.appendChild(strong);
        error = true;
    } else if (form['email'].value.length > 64) {
        td = form['email'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Thats Too long';
        td.appendChild(strong);
        error = true;
    }
    if (form['text'].value == '') {
        td = form['text'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Cant be empty';
        td.appendChild(strong);
        error = true;
    } else if (form['text'].value.length > 280) {
        td = form['text'].parentElement;
        strong = document.createElement('strong');
        strong.className = 'form-error';
        strong.innerHTML = 'Thats Too long<br>Nobody is going to read that';
        td.appendChild(strong);
        error = true;
    }

    if (error) {
        return false;
    }

    pos = addMarkerOverlay.getPosition();
    pos[0] = Math.round(pos[0]);
    pos[1] = Math.round(pos[1]);
    // pos = ol.proj.transform(overlay.getPosition(), 'EPSG:3857', 'EPSG:4326');
    form['pos'].value = JSON.stringify(pos);

    var http = new XMLHttpRequest();
    var params = new FormData(form);

    http.open("POST", form['action'], true);
    http.onreadystatechange = function() {
        if(http.readyState == 4 && http.status == 200) {
            response = JSON.parse(http.responseText);
            if (response['accepted'] == true && response['error'] == true) {
                alert(response['error_text']);
            } else if (response['accepted'] == false) {
                for (var key in response['errors']) {
                    td = form[key].parentElement;
                    strong = document.createElement('strong');
                    strong.className = 'form-error';
                    strong.innerHTML = response['errors'][key];
                    td.appendChild(strong);
                }
                document.getElementById('captcha').src = "/captcha.php?t=" + new Date().getTime();
            } else {
                if (form['edit'].value != '' && isAdmin) {
                    addMarkerOverlay.setPosition(undefined);
                    showMarker(form['edit'].value, pos);
                } else {
                    content = document.getElementById('addMarker-content');
                    content.innerHTML = '<div style="width: 34ch">request had been send to the admin<br/>your pin wil apear when aproved</div>';
                    button = document.getElementById('add-marker');
                    button.parentElement.removeChild(button);
                    adding = false;
                }
            }
        }
    }
    http.send(params);
    return false;
}

function main() {
    loadMarkers();
    map.set('target', 'map');

    if (isMobile()) {
        addMarker = document.getElementById('addMarker');
        addMarker.className = 'mobileBottom';

        marker = document.getElementById('Marker');
        marker.className = 'mobileBottom';

        addMarkerOverlay.set('element', document.getElementById('MobileAddMarker'));
        MarkerOverlay.set('element', document.getElementById('MobileMarker'));

    } else {
        addMarkerOverlay.set('element', document.getElementById('addMarker'));
        MarkerOverlay.set('element', document.getElementById('Marker'));
    }
}
document.addEventListener('DOMContentLoaded', main, false);