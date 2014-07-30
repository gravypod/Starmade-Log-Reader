<?php
	
	$files = "/home/starmade/logs/log.txt.0";
	$lines = 29;
	
	if (isset($_GET["latest"])) {
		die(`tail -n $lines $files`);
	}
	
?>
<html>
	<head>
		<title>Starmade Logs</title>
		<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Ubuntu:regular,bold&subset=Latin,Cyrillic">
		<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootswatch/3.2.0+1/superhero/bootstrap.min.css"></link>
	</head>
	<body>
		<div class="container">
			<div class="page-header">
				<h1>Starmade Logs <small>For The Better Admins</small></h1>
			</div>
			<div class="well well-lg">
				<table class="table table-hover">
					<thead>
						<tr>
							<th>Time</th>
							<th>Message</th>
						</tr>
					</thead>
					<tbody id="msgtable">
						
					</tbody>
					
				</table>
			</div>
		</div>
	</body>
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		var LOG_REQUEST = window.location.href;
		var ONE_HOUR = 60 * 60 * 1000;
		var HR_TO_OLD = 1;
		var LAST_MESSAGE = null;
		var ON_SCREEN = [];
		
		var MessageColors = {
			"[CC]": 'red',
			"[SQL] DESPAWNING": 'blue',
			"waiting for pong true": 'yellow'
		}
		
		var filteredMessages = [
			"placement in unloaded segment",
			"Executing Shopping Buy",
			"spawning inventory at",
			"A segment for",
			"KINEMATIC",
			"[CATALOGMANAGER][SERVER] NO",
			"[SERVER-LOCAL-ADMIN] END; broadcasted",
			"[SERVER][ERROR] could not wisper. client not found: ",
			"[CONTROLLERSTATE] ",
			"SERIALIZED PROXIMITY",
			"] FACTION BLOCK ADDED TO Ship",
			"checking sectors to plan activity",
			"Exception: tried to remove controller to invalid type: TYPE_NONE",
			"Shield hit with effect 'overdriveeffect': ",
			"Cleaned up Physics, because repository is full",
			"LEFT CHILD ON",
			"[FACTION]",
			"[SERVER] MISSILE UPDATE; Missile Count:",
			"[MISSILE]",
			"[DATABASE] UPDATING SECTOR:",
			"[EXPLOSION] ",
			"[NT] Big FullUpdate:",
			"[SERVER][FACTION] Hostlie action detected from NEUTRAL PlayerCharacter",
			"[AI]",
			"[SECTOR] TOUCHED",
			" SERVER SECTOR WRITING STARTED ",
			"READLING ALL BLUEPRINTS",
			"[SERVER][DEBUG] WRITTEN ",
			"WRITING ENTITY TAG: ",
			"[SEND][SERVERMESSAGE] [SERVERMSG (type 0): ",
			"LOADED SECTOR",
			"Executing Shopping Sell", 
			" loaded activation state: ActBuffer ", 
			" GENERATING TRANSIENT SECTOR DATA ",
			"[SERVER] snapped player controlled object Ship",
			"(blocks: 0 CANNOT CONSUME: current power:",
			" FINE: [SERVERSOCKET] Incoming connection: ",
			" FINE: Client 'Info-Pinger (server-lists)'",
			"STDERR:     at",
			"STDERR: [PlayerFactionManager]",
			"STDOUT: [SERVER][UNIVERSE] LOADING SECTOR...",
			"STDERR: [NT] Big FullUpdate:",
			"STDERR: [IOFileManager]",
			"STDERR: [IFFileManager]",
			"STDERR: [DELETE][Server(0)] Sendable",
			"STDERR: SENDING UPDATE FOR",
			"STDERR: [SECTOR][CLEANUP]",
			"STDERR: [SERVER] waiting for sector",
			"STDERR: Server(0) [SegmentController][setCurrentBlockController]",
			"STDERR: [SERVER] SERIALIZED PROXIMITY",
			"STDERR: [DOCK] NOW DOCKING:",
			"STDERR: [SEGCON] NOW DOCKED ON",
			"STDERR: [DOCKINGBLOCK] IS DOCKABLE",
			"STDERR: [AI] DISBANDING: Waiting for all to unload.",
			"STDERR: [DockingBeamHandler] CANNOT DOCK:",
			"STDOUT: Zipping folder",
			"STDERR: PUTTING",
			"STDERR: [CONTROLLERSTATE]",
			"STDERR: [SQL][WANRING]",
			"STDERR: [SIM]",
			"STDERR: [DOCKING]",
			"STDERR: at ",
			"STDERR: [SERVER][SEGMENTCONTROLLER]",
			"[DOCK]",
			"org.schema",
			"[DOCKINGBLOCK]",
			"WARNING: Possible attempt to hack controls of another player.",
			"REMOVED CACHE:",
			"STDERR: [SERVER] WARNING: WRITING ENTITY",
			"STDERR: [SERVER][SECTOR]",
			"STDERR: [METAITEM]",
			"STDERR: [SERVER][SENSEGMENTCONTROLLER]",
			"STDERR: at org",
			"STDERR: This cant be controlled by Ship",
			"STDERR: [TAG]",
			"STDERR: [ELEMENTMANAGER]",
			"DOCK",
			"RECEVING WISPER",
			"[SERVER][UPDATE]",
			"[UNIVERSE]",
			"[CHARACTER]",
			"STDERR: NEW",
			"[SENDABLESEGMENTVONTROLLER][WRITE]",
			"[DELETE][Server(0)] Sendable",
			"[PROVIDER] RE-REQUEST NOTED:",
			"ADDING REREQUESTING SEGMENT",
			"[SERVER][SENSEGMENTCONTROLLER]",
			"[UploadController]",
			"[SERVER] waiting for sector",
			"[SECTOR][CLEANUP]",
			"[CONTROLLERSTATE][REMOVE-UNIT]",
			"[GRAVITY] Ship",
			"[SEGMENT-CONTROLLER]",
			"[SERVER] BROADCAST MISSILE UPDATE",
			"[CONTROLLER][ADD-UNIT]",
			"[SEGMENT][Server(0)]",
			"[INVENTORY] Server(0)",
			"[REMOTESECTOR] ITEM ADDED:",
			"Picked up:",
			"[SERVER][SEGMENTCONTROLLER]",
		];

		function searchArray(needle, haystack) {
			
			for(var i = 0; i < haystack.length; i++) {
				if(needle.indexOf(haystack[i]) > -1) {
					return true;
				}
			}
			return false;
		}

		
		var UPDATE_LOG = function () {
			$.get(LOG_REQUEST, {latest: ""}, function (data) {
				var lines = data.split("\n");
				lines.forEach(function (e) {
					
					if (e.indexOf("[") !== 0 || searchArray(e, filteredMessages)) {
						return;
					}
					AddLine(e);
				});
			});
			setTimeout(UPDATE_LOG, 600); 
		};
		
		var LOG_PURGE = function () {
			RemoveOld();
			setTimeout(LOG_PURGE, 600);
		};
		
		function AddLine(line) {
			if (ON_SCREEN.indexOf(line) != -1) {
				return;
			}
			ON_SCREEN.push(line);
			var tr = LogMessage(line);
			if (LAST_MESSAGE === null) {
				var element = $("#msgtable");
				tr.appendTo(element);
				LAST_MESSAGE = tr;
			} else {
				LAST_MESSAGE.before(tr);
				LAST_MESSAGE = tr;
			}
			for (var type in MessageColors) {
				if (line.indexOf(type) != -1) {
					LAST_MESSAGE.css('color', MessageColors[type]);
				}
			}
		}
		
		function RemoveOld() {
			$("tr").each(function (index) {
				var e = $(this);
				var timeDistance = Date.now() - e.attr("created");
				if (timeDistance > (ONE_HOUR * HR_TO_OLD)) {
					console.log("Removing");
					remove(ON_SCREEN, e.text());
					e.remove();
				}
			});
		}
		
		function LogMessage(line) {
			var tr = $("<tr>");
			var timestamp = $("<td>");
			var message = $("<td>");
			
			tr.append(timestamp);
			tr.append(message);
			var firstBraket = line.indexOf("]");
			timestamp.text(line.substring(1, firstBraket).split(" ")[1]);
			message.text(line.substring(firstBraket + 1).trim());
			tr.attr("created", Date.now());
			return tr;
		}
		
		function remove(arr, item) {
			for(var i = arr.length; i--;) {
				if(arr[i] === item) {
					arr.splice(i, 1);
				}
			}
		}
		
		UPDATE_LOG();
		LOG_PURGE();
	</script>
</html>