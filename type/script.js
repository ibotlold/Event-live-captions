// Status check:
// 1 - informational
// 2 - succesful
// 3 - redirect
// 4 - client error
// 5 - server error
var timer = setTimeout(function() {$('.wrapper#loader').show();}, 1000);
var event = {id: ''};
var eventStartedFlag = false;
var systemFlag = false;
var recognition;
var recognitionStarted = false;
var recognisedMsg;
$(function() {
	clearTimeout(timer);
	$('.wrapper#loader').hide();
	var path = document.location.search;
	var pattern = /(\w){7}/;
	if (path.match(pattern)) {
		event.id = path.match(pattern)[0];
	}
	if (!$('.status[name=2]').attr('disabled')) {
		eventStartedFlag = true;
	}
	loadNewInput();
});

$('.status').on('click', 'input', function(event) {
	changeEventStatus(event.target.name);
});

$(document).on('keydown','textarea', function(event) {
	var key = event.which || event.keyCode;
	if (event.key.match(/^[0-9a-zA-Zа-яёА-ЯЁ]{1}$/) && event.target.value.length == 0 && !systemFlag) {
		sendMessage($(event.target.parentNode));
	}
	if (key == 13 || key == 8 || key == 46 || (key == 32 && !systemFlag)) {
		if (event.target.value.length > 0) {
			sendMessage($(event.target.parentNode));
			if (key == 13) {
				loadNewInput();
				return false;
			}
		} else {
			return false;
		}
	}
	if (key == 113) {
		systemFlag = !systemFlag;
		if (systemFlag) {
			$('textarea:focus').addClass('system');
		} else {
			$('textarea:focus').removeClass('system');
		}
	}
	return true;
});

$(document).on('keyup','textarea', function(event) {
	var key = event.which || event.keyCode;
	if (key == 8 || key == 46) {
		if (event.target.value.length == 0) {
			deleteMessage($(event.target.parentNode));
		}
	}
	return true;
}); 

function sendRequest(actStatus, typeAction, fdata, fDone, fFail) {
	$.ajax({
		url: '/type/action.php',
		type: 'POST',
		dataType: 'json',
		data: {eventId: event.id, status: actStatus, action: typeAction, data: fdata},
	})
	.done(function(data) {
		if (fDone) {
			if (data.status < 4) {
				fDone(data);
			} else {
				if (fFail) {
					fFail(data);
				}
			}
		}
	})
	.fail(function(jqXHR, text, error) {
		if (fFail) {
			fFail(error);
		}
	})
	.always(function() {
	});		
}

function changeEventStatus(status) {
	sendRequest(2,'changeEventStatus',status, function() {
		$('.status input').prop('disabled', false);
		$(event.target).prop('disabled', true);
		$('.status input[name='+status+']').prop('disabled', 'true');
		if (status == 2) {
			eventStartedFlag = true;
		} else {
			eventStartedFlag = false;
		}
	});
}

function sendMessage(target, isSystem) {
	if (!eventStartedFlag) {
		changeEventStatus(2);
	}
	if (target.attr('id')) {
		//изменить сообщение
		sendRequest(2, 'editMessage', {text: target.children('textarea').val(), timeStamp: target.attr('id')}, null, null)
	} else {
		//Новое сообщение
		sendRequest(2, 'newMessage', {system: systemFlag}, function(result) {
			var time = new Date(parseInt(result.data));
			time.getHoursMinutes = function() {
				var result = (this.getHours().toString().length > 1) ? this.getHours() : '0' + this.getHours();
				return ''.concat(result,':',(this.getMinutes().toString().length > 1) ? this.getMinutes() : '0'+ this.getMinutes());
			};
			var timestampLabel = target.children('.timestamp');
			timestampLabel.html(time.getHoursMinutes());
			target.attr('id', time.getTime());
			recognisedMsg = null;
		}, null)
	}
}

function deleteMessage(target) {
	if (target.attr('id')) {
		sendRequest(2, 'deleteMessage', {timeStamp: target.attr('id')}, null, null);
	}
	target.remove();
	$('.caption.label').last().children('textarea').focus();
	if ($('.caption.label').length == 0) {
		loadNewInput();
	}
}

function loadNewInput() {
	systemFlag = false;
	if ($('.caption.label').last().attr('id') || $('.caption.label').last().length == 0) {
		$.ajax({
			url: '/type/input.html',
			dataType: 'html',
			context: document.body,
		})
		.done(function(html) {
			$('.wrapper#loader').hide();
			$(this).append(html);
			$('html, body').scrollTop($(document).height());
			$('.caption.label').last().children('textarea').focus();
		});
	} else {
		$('.caption.label').last().children('textarea').focus();
	}
}

//speech api
$(document).on('click', '.speechRecord', function(event) {
	if (!recognition) {
		if (!('webkitSpeechRecognition' in window)) {
			upgrage();
		} else {
			recognition = new webkitSpeechRecognition();
			recognition.continuous = true;
			recognition.interimResults = true;
			recognition.lang = 'ru-RU';
			recognition.onstart = function() {
				$(event.target).attr('id','active');
			}
			recognition.onresult = function(event) {
				if (typeof(event.results) == 'undefined') {
					recognition.stop();
					upgrade();
					return;
				}
				//console.log(event);
				for(var i = event.resultIndex; i < event.results.length; i++){
					for (var k = 0; k < event.results[i].length; k++) {
						if (event.results[i][k].confidence > 0.7) {
							//console.log(event.results[i][k].transcript);
							var textareaMsg = $('.caption.label textarea:focus').val();
							if (textareaMsg != event.results[i][k].transcript) {
								console.log(event.results[i]);
								$('.caption.label textarea:focus').val(event.results[i][k].transcript);
								if (event.results[i][k].transcript.length > 0) {
									if (!recognisedMsg) {
										recognisedMsg = sendMessage($('.caption.label textarea:focus').parent());
									}
								}
								
							}
						}
					}
					if (event.results[i].isFinal) {
						recognition.stop();
					}
				}
			}
			recognition.onerror = function(event) {
				console.log(event);
			}
			recognition.onend = function() {
				if ($('.caption.label textarea:focus').length > 0) {
					if ($('.caption.label textarea:focus').val().length > 0) {
						sendMessage($('.caption.label textarea:focus').parent());
					}
				}
				loadNewInput();
				if (recognitionStarted) {
					recognition.start();
				} else {
					$(event.target).removeAttr('id');
				}
			}
		}
	}
	if ($(event.target).attr('id') == 'active') {
		recognitionStarted = false;
		recognition.stop();
	} else {
		recognition.start();
		recognitionStarted = true;
		$('.caption.label').last().children('textarea').focus()
	}
});