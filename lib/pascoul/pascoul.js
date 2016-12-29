/**
	PHP Asynchronous Loader - Pascoul
	- complementary JS file -
	Long-running processes with user feedback
	Requirements: PHP5, HTML 5 compatible browser
	
	@author: Christian Seip <christian.seip@gdi-service.de>
	based on:
	- http://www.htmlgoodies.com/beyond/php/show-progress-report-for-long-running-php-scripts.html
	- http://stackoverflow.com/questions/2190801/passing-parameters-to-javascript-files
*/
//Html elements ids
var progress_id = 'progressor',
		span_id = 'percentage',
		log_id = 'log',
		progressDiv_id = 'progress',
		readyButton_id = 'ready_button';

var Pascoul = Pascoul || (function(){
    var _args = {}; // private
	var es;
	
	//Html elements settings
	var divLogBoxStyle;
	var progressDivTitle;
	var percentageSpanStyle;
	
    return {
        init : function(Args) {
			//get params
            _args = Args;
			divLogBoxStyle = _args['html_params']['divLogBoxStyle'];
			progressDivTitle = _args['html_params']['progressDivTitle'];
			progressElemStyle = _args['html_params']['progressElemStyle'];
      percentageSpanStyle = _args['html_params']['percentageSpanStyle'];
        },
		startTask : function() {
			//If aborted before, firstly clear the log
			clearLog();

			//make progress div
			var progressDiv = document.createElement('div');
			progressDiv.id=progressDiv_id;
			document.body.appendChild(progressDiv);
			
			//build everything in progress div
			//1. heading/title
			progressDiv.innerHTML = "<h2>" + progressDivTitle + "</h2>";

			//2. box with log elements
			var logDiv = document.createElement('div');
			logDiv.id=log_id;
			logDiv.style=divLogBoxStyle;
			progressDiv.appendChild(logDiv);

			//3. progress bar
			progressDiv.innerHTML += "<br/>";
			var progressElem = document.createElement("progress");
			progressElem.id=progress_id;
			progressElem.value="0";
			progressElem.max='100';
			progressElem.style=progressElemStyle;
			progressDiv.appendChild(progressElem);

			var percentageSpan = document.createElement("span");
			percentageSpan.id=span_id;
			percentageSpan.style=percentageSpanStyle;
			percentageSpan.value="0";
			progressDiv.appendChild(percentageSpan);

			var readyButton = document.createElement("input");
			readyButton.id = readyButton_id;
			readyButton.type = 'button';
			readyButton.name = 'Fertig';
			readyButton.value = 'Fertig';
			readyButton.addEventListener("click", function(e) {
				var progressDiv = document.getElementById(progressDiv_id);
				document.body.removeChild(progressDiv);
			});
			progressDiv.appendChild(readyButton);

			if(typeof(EventSource) !== "undefined") {
				//build query url with GET parameters
				var url = _args['url_params']['url'] + "?";
				//FIXME? Should the valies be url escaped? (e.g. with encodeURIComponent(_args['url_params'][url_param]))
				for (url_param in _args['url_params']) {//key: url_param, value: _args['url_params'][url_param]
					if(url_param !== "url") url += url_param + "=" + _args['url_params'][url_param] + "&";
				}
				//console.log(url);
				es = new EventSource(url);
		  
				//a message is received
				es.addEventListener('message', function(e) {
					var result = JSON.parse(e.data),
							pBar = document.getElementById(progress_id),
							perc = document.getElementById(span_id),
							pBut = document.getElementById(readyButton_id);
					  
					addLog(result.message);
					  
					if(e.lastEventId == 'CLOSE') {
						//addLog('Received CLOSE closing');
						es.close();
						pBar.value = pBar.max; //max out the progress bar
						perc.innerHTML   = "100%";//goto 100%
						pBut.style.display = 'inline';
					}
					else {
						var pBar = document.getElementById(progress_id);
						if (result.progress!==null) {
							pBar.value = result.progress;
							var perc = document.getElementById(span_id);
							perc.innerHTML   = result.progress  + "%";
							perc.style.width = (Math.floor(pBar.clientWidth * (result.progress/100)) + 15) + 'px';
						}
					}
				});
				//Connection opened received
				es.addEventListener("open", function(e) {
					addLog("Connection was opened.");
				});
				//An error occured
				es.addEventListener('error', function(e) {
					addLog('Error occurred');
					es.close();
				});
			}
			else {
				//IE: just run "window.location = 'converter/xmi2db.php?truncate=' + truncate + '&file=' + file + '&schema=' + schema + '&basepackage=' + basepkg + '&argo=' + argo;" then?
				addLog("Sorry, your browser does not support server-sent events...");
			}
		},
		stopTask : function() {
			es.close();
			addLog('Interrupted');
		}
    };
}());

function addLog(message) {
    var r = document.getElementById(log_id);
    r.innerHTML += message + '<br>';
    r.scrollTop = r.scrollHeight;
}

function clearLog() {
	var logNode = document.getElementById(log_id);
	var progNode = document.getElementById(progress_id);
	var spanNode = document.getElementById(span_id);
    if (logNode !== null) {
		logNode.parentNode.removeChild(logNode);
		progNode.parentNode.removeChild(progNode);
		spanNode.parentNode.removeChild(spanNode);
	}
}