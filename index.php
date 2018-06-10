<!doctype html>
<html lang="en">
    <head>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
        <meta content="utf-8" http-equiv="encoding">
        <title>Repronik Map Test</title>
        <script src="resources/javascript/openlayers.js" type="text/javascript"></script>
        <script type="text/javascript" src="resources/javascript/main.js"></script>
        <link rel="stylesheet" type="text/css" href="resources/css/openlayers.css">
        <link rel="stylesheet" type="text/css" href="resources/css/main.css">
    </head>
    <body>
        <div id="map" class="map"></div>
        <input type="button" value="Add marker" onclick="toggleAddMarker(this);" id="add-marker" >
        <div id="adminCommands">
            <input class="adminButton" type="button" command="0" value="show unprocessed" onclick="adminMarkers(this);" >
            <input class="adminButton" type="button" command="2" value="show aproved" onclick="adminMarkers(this);">
            <input class="adminButton" type="button" command="1" value="show rejected" onclick="adminMarkers(this);" >
        </div>
        <div id="ViewCenter"></div>
        <div id="scaleLine" onclick="changeUnit();"></div>

        <div id="MobileMarker" class="ol-popup"></div>
        <div id="MobileAddMarker" class="ol-popup">Adding Marker</div>

        <div id="Marker" class="ol-popup">
            <div class="blocks">
                <img src="/resources/images/nsfw.png" id="nsfwOverlay" onclick="showImg(this);">
                <img src="" id="markerPhoto">
            </div>
            <div class="blocks">
                <div class="row">
                    <div class="blocks">
                        <label for="markerName">Name:</label>
                    </div>
                    <div id="markerName" class="blocks"></div>
                </div>
                <div class="row">
                    <div class="blocks">
                        <label for="markerEmail">Email:</label>
                    </div>
                    <div id="markerEmail" class="blocks"></div>
                </div>
                <div id="markerText" class="row"></div>
                <div id="judgeButtons" class="row">
                    <button id="accept" onclick="procesMarker(this);" >ACCEPT</button>
                    <button id="edit" onclick="procesMarker(this);" >EDIT</button>
                    <button id="reject" onclick="procesMarker(this);" >REJECT</button>
                </div>
                <div class="row" id="useredit">
                    <input type="submit" value="Edit" onclick="useredit(this)"><br>
                </div>
            </div>
        </div>

        <div id="addMarker" class="ol-popup">
            <div id="addMarker-content">
                <form name="addMarker-form" method="POST" action="addMarker.php" enctype="multipart/form-data" onsubmit="checkForm();return false;">
                    <input type="hidden" name="pos">
                    <input id="addMarkerEdit" type="hidden" name="edit">
                    <!-- <input id="addMarkerRemoveImage" type="hidden" name="removeImage"> -->
                    <!-- <input id="addMarkerUserEdit" type="hidden" name="useredit"> -->

                    <div class="row">
                        <div class="blocks">
                            <label for="addMarkerName">Name:</label>
                        </div>
                        <div class="blocks">
                            <input id="addMarkerName" type="text" name="name" placeholder="Name" maxlength="64" required >
                        </div>
                    </div>

                    <div class="row">
                        <div class="blocks">
                            <label for="addMarkerEmail">Email:</label>
                        </div>
                        <div class="blocks">
                            <input id="addMarkerEmail" type="text" name="email" placeholder="Email" maxlength="64" required >
                        </div>
                    </div>

                    <div class="row" id="addMarkerFileRow">
                        <div class="blocks">
                            <label for="addMarkerName">Image:</label>
                        </div>
                        <div class="blocks">
                            <input id="addMarkerFile" type="file" name="file" onchange="showNSFW(this);">
                        </div>
                    </div>

                    <div class="row" id="addMarkerNsfwRow">
                        <div class="blocks">
                            <label for="addMarkerNsfw">NSFW:</label>
                        </div>
                        <div class="blocks">
                            <input id="addMarkerNsfw" type="checkbox" name="nsfw">
                        </div>
                    </div>
                    <div class="row" id="addMarkerRemoveImageRow">
                        <input type="button" id="addMarkerRemoveImage" value="remove image" onclick="removeimage(this);">
                    </div>

                    <div class="row">
                        <textarea id="addMarkerText" name="text" rows="7" cols="40" maxlength="280" placeholder="Something about yourself" required ></textarea>
                    </div>

                    <div class="row" id="addMarkerCaptcha">
                        <div class="blocks">
                            <img id="captcha" src="/captcha.php">
                        </div>
                        <div class="blocks">
                            <input type="text" name="captcha" placeholder="Captcha" maxlength="5" style="width: 10ch" required >
                        </div>
                    </div>

                    <div class="row">
                        <div class="blocks">
                            <label for="addMarkerPassword">Password:</label>
                        </div>
                        <div class="blocks">
                            <input id="addMarkerPassword" type="text" name="password" minlength="8" maxlength="64" required >
                        </div>
                    </div>

                    <div class="row">
                        <input type="submit" value="Submit"><br>
                    </div>
                </form>
            </div>
        </div>
        <div id="InfoOverlay" class="ol-popup">request had been send to the admin<br/>your pin wil apear when aproved</div>
    </body>
</html>
