<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel='shortcut icon' type='image/x-icon' href="{{ asset('map/images/favicon/favicon.ico') }}">
	<link rel="android-chrome" sizes="192x192" href="{{ asset('map/images/favicon/android-chrome-192x192.png') }}">
	<link rel="android-chrome" sizes="512x512" href="{{ asset('map/images/favicon/android-chrome-512x512.png') }}">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('map/images/favicon/apple-touch-icon.png') }}">
	<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('map/images/favicon/favicon-32x32.png') }}">
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('map/images/favicon/favicon-16x16.png') }}">
	<link rel="manifest" href="/site.webmanifest">

	<!--- STYLES --->
	<link href="{{ asset('map/css/index.css') }}" type="text/css" rel="stylesheet">
	<!-- MAPBOX-GL -->
	<script src="{{ asset('map/js/libs/mapbox/mapbox-gl.js') }}"></script>
	<link href="{{ asset('map/css/mapbox-gl.css') }}" type="text/css" rel='stylesheet' />
	<!-- TURF E MAPBOX-GL-DRAW -->
	<script src="{{ asset('map/js/libs/mapbox/turf.min.js') }}"></script>
	<script src="{{ asset('map/js/libs/mapbox/mapbox-gl-draw.js') }}"></script>
	<link href="{{ asset('map/css/mapbox-gl-draw.css') }}" type="text/css" rel="stylesheet">
	<!-- MAPBOX-GL-GEOCODER -->
	<script src="{{ asset('map/js/libs/mapbox/mapbox-gl-geocoder.min.js') }}"></script>
	<link href="{{ asset('map/css/mapbox-gl-geocoder.css') }}" type="text/css" rel="stylesheet">
	<!-- Promise polyfill script required to use Mapbox GL Geocoder in IE 11 -->
	<script src="{{ asset('map/js/libs/mapbox/es6-promise.min.js') }}"></script>
	<script src="{{ asset('map/js/libs/mapbox/es6-promise.auto.min.js') }}"></script>
	<!-- AXIOS -->
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<!-- HTML2CANVAS -->
	<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.0.0-rc.5/dist/html2canvas.min.js"></script>

	<title>{{ env('APP_NAME'); }}</title>
</head>

<body>
	@if(isset($flightplan))
    	<div id="map" flightplan="{{ json_encode($flightplan) }}"></div>
	@elseif(isset($log))
        <div id="map" log="{{ json_encode($log) }}"></div>
    @else
        <div id="map"></div>
    @endif
	
	<script>
        // Token gerado para uso no MAPBOX-GL
        mapboxgl.accessToken = 'pk.eyJ1IjoidGF1YWNhYnJlaXJhIiwiYSI6ImNrcHgxcG9jeTFneWgydnM0cjE3OHQ2MDIifQ.saPpiLcsBQnqVlRrQrcCIQ';

        // === POSIÇÃO INICIAL NO MAPA === //
        home = [-47.926063, -15.841060];

        var coordinatesLongLat;
        var initialPosition = [];
        //var longestEdgeLongLat;
        var farthestVertexLongLat;
        var selectedPosition;

        var finalDestination = [];
        var initialFinalPath = [];
        var initialPath = [];

        // Criando um objeto mapa
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/satellite-v9',
            zoom: 14.5,
            center: home, // Longitude e latitude
            preserveDrawingBuffer: true
        });

        var draw = new MapboxDraw({
            displayControlsDefault: false,
            controls: {
                polygon: false,
                trash: false
            },
            defaultMode: 'draw_polygon'
        });

        map.addControl(draw); // Adicionando o controle de desenho ao mapa

        // ======================================================= ROUTINES ======================================================= //

        window.onload = async function () {

            const flight_plan = document.getElementById('map').getAttribute('flightplan');
            const log = document.getElementById('map').getAttribute('log');

            if(flight_plan) {
                importTXT(flight_plan);
            } else if(log) {
                importKML(log);
            } else {
                return;
            }
        }

        async function importTXT(data) {

                // Limpando layers, campos e polígono
                cleanLayers();
                cleanPolygon();

                // Conteúdo completo do arquivo
                var contents = JSON.parse(data);

                // Quebrando as linhas do arquivo em um array
                var lines = contents.split("\n");

                // Acessando a posição inicial (home) contida no arquivo
                var txtHome = lines[1].split("\t");
                home = [Number(txtHome[9]), Number(txtHome[8])];

                // Acessando a velocidade contida no arquivo e preenchendo o campo no form
                var txtSpeed = lines[3].split("\t");

                // Acessando a altitude contida no arquivo e preenchendo o campo no form
                var txtAltitude = lines[2].split("\t");

                // Array que armazenará todos os waypoints da rota do arquivo
                txtPath = [];
                index = 0;

                // Quebrando todas as linhas nos espaços \t
                for (i = 4; i < lines.length - 2; i++) {
                    line = lines[i].split("\t");

                    // Somente os waypoints com latitude e longitude são considerados, ou seja, código 16
                    // Os waypoints de código 183 (gatilho do dispenser) tem lat/long zerados e não podem ser
                    // adicionados ao desenho da rota
                    if (Number(line[3]) == 16) {
                        // As posições de latitude e longitude estão nos índices 9 e 8 de cada linha
                        txtPath[index] = [Number(line[9]), Number(line[8])];
                        index++
                    }
                }

                // Array que armazenará todas as coordenadas do polígono extraídas a partir do arquivo
                txtArea = [];
                index = 0;

                // Quebrando a última linha para acessar as coordenadas do polígono
                txtPolygon = lines[lines.length - 1].split("\t");

                // Acessando todas as coordenadas
                for (i = 1; i < txtPolygon.length - 1; i++) {
                    txtLatLong = txtPolygon[i].split(",");
                    txtArea[index] = [Number(txtLatLong[0]), Number(txtLatLong[1])];
                    index++;
                }

                // Acessando o centroide da área para posicionar no mapa
                var polygon = turf.polygon([txtArea]);
                var centroid = turf.coordAll(turf.centroid(polygon));

                // Direcionando o mapa
                map.flyTo({
                    center: [
                        centroid[0][0], centroid[0][1]
                    ],
                    essential: true
                });

                // Desenhando a rota e calculando sua distância
                drawTxtPath(txtPath);
                calculateTxtDistance(txtPath);

                // Desenhando o polígono e calculando sua área
                drawTxtArea(txtArea);
                calculateTxtArea();

        }

        function importKML(contents) {

            cleanLayers();
            cleanPolygon();

            // Localizando as tags <coordinates> dentro do arquivo
            var coordinates = contents.substring(
                contents.search("<coordinates>") + 13,
                contents.search("</coordinates>")
            );

            // Quebrando todas as coordenadas do polígono
            coordinates = coordinates.split("\n");

            // Array que irá armazenar as coordenadas da área
            kmlArea = [];

            // Percorrendo todas as coordenadas e quebrando as informações de lat e long
            for (i = 0; i < coordinates.length - 1; i++) {
                //console.log(coordinates[i]);

                latLong = coordinates[i].split(",");
                kmlArea[i] = [Number(latLong[0]), Number(latLong[1])];
            }

            // Certificando-se de que a primeira e a última posição de kmlArea são idênticas
            if (kmlArea[0][0] == kmlArea[kmlArea.length - 1][0] && kmlArea[0][1] == kmlArea[kmlArea.length - 1][1]) {
                //console.log("São IGUAIS!");
            } else {
                //console.log("NÃO SÃO IGUAIS!");
                kmlArea[i] = kmlArea[0];
            }

            console.log(kmlArea[0]);
            //console.log(kmlArea[kmlArea.length - 1]);

            home = kmlArea[0];

            // Acessando o centroide da área para posicionar no mapa
            var polygon = turf.polygon([kmlArea]);
            var centroid = turf.coordAll(turf.centroid(polygon));

            // Direcionando o mapa
            map.flyTo({
                center: [
                    centroid[0][0], centroid[0][1]
                ],
                essential: true
            });

            drawTxtArea(kmlArea);
            drawTxtPath(kmlArea);
        }

        function drawTxtArea(txtArea) {

            var objArea = {
                'type': 'Polygon',
                'coordinates': [
                    txtArea
                ]
            }
            draw.add(objArea);
        }

        function drawTxtPath(txtPath) {

            // Limpando todos os layers
            cleanLayers();

            // Novos sources e layers são adicionados apenas se ainda não existem no mapa
            var objBF = {
                'type': 'geojson',
                'data': {
                    'type': 'Feature',
                    'properties': {},
                    'geometry': {
                        'type': 'MultiLineString',
                        'coordinates': [
                            txtPath
                        ]
                    }
                }
            }

            map.on('load', function(){
                map.addSource('txtPath', objBF);
                map.addLayer({
                    'id': 'txtPath',
                    'type': 'line',
                    'source': 'txtPath',
                    'layout': {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    'paint': {
                        'line-color': '#fcba03',
                        'line-width': 3
                    }
                });
            })
            
        }

        // ======================================================= CLEAN LAYERS ======================================================= //

        function cleanLayers() {
            var layers = ['routePhase01', 'routePhase02', 'routePhase03', 'routePoints01', 'bfPhase02', 'txtPath', 'intermediatePoints', 'wp01', 'wp02', 'wp03', 'bp01'];
            // Limpando todos os layers contidos no mapa
            for (i = 0; i < layers.length; i++) {
                var mapLayer = map.getLayer(layers[i]);

                if (typeof mapLayer !== 'undefined') {
                    map.removeLayer(layers[i]).removeSource(layers[i]);
                }
            }
        }

        function cleanLayerById(id) {
            var mapLayer = map.getLayer(id);

            if (typeof mapLayer !== 'undefined') {
                map.removeLayer(id).removeSource(id);
            }
        }

        function cleanPolygon() {
            // Limpando o polígono
            draw.deleteAll();
        }
    </script>
</body>

</html>