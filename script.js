var timer = setTimeout(function() {$('.wrapper#loader').show();}, 1000);
const initialDocTitle = document.title;
var eventIdentificator = '';
$(function() {
	clearTimeout(timer);
	$('.wrapper#loader').hide();
	var path = document.location.pathname;
	var pattern = /^\/((\w){7}$)/;
	if (pattern.test(path)) {
		eventIdentificator = getEventIdFromURL();
		sendRequest(2, 'getCurrentState', null, function(data) {
			$('.wrapper#loader').hide();
			$('.auth-code').remove();
			window.history.pushState(data.data, data.data.event.title, "/"+data.data.event.id);
			document.title = data.data.event.title;
			loadCaption();
			console.log(data);
		},null);
	} else {
		authCodeLoad();
	}
});
window.onpopstate = function(event) {
	if (event.state == null) {
		authCodeLoad();
		$('.caption').remove();
	} else {
		eventIdentificator = getEventIdFromURL();
		clearTimeout(timer);
		$('.auth-code').remove();
		loadCaption();
	}
};

function sendRequest(actStatus, typeAction, sendData, fDone, fFail) {
	$.ajax({
		url: '/event.php',
		type: 'POST',
		dataType: 'json',
		data: {eventId: eventIdentificator, status: actStatus, action: typeAction, data: sendData},
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

function authCodeLoad (state) {
	if (!$('.auth-code').length) {
		document.title = initialDocTitle;
		timer = setTimeout(function() {$('.wrapper#loader').show();}, 1000);
		$.ajax({
			url: "auth-code.html",
			context: document.body,
			dataType: 'html'
		}).done(function(html) {
			clearTimeout(timer);
			$('.wrapper#loader').hide();
			$(this).append(html);
		});
	}
	$(document).on('keydown click',(function(event) {
		var key = event.which || event.keyCode;
		if (!history.state) {
			if (key == 1 ||key >= 48 && key <= 57 || key >= 96 && key <= 105) {
				var emptyChar = 0;
				var oneEmpty = false;
				$('input').each(function(index, el) {
					if ($(this).val().length == 0) {
						emptyChar = index;
						if (oneEmpty) {
							$('#char'+(emptyChar)).focus();
							return false;
						}
						oneEmpty = true;
						$(this).val(event.key);
						$('#char'+(emptyChar)).focus();
						if (key == 1) {
							return false;
						}
						if (authCheck()) {
							$('input').prop('disabled', 'true')
						}
					}
				});
				return false;
			}
			return true;
		}
		return true;
	}));
	$('body').on('focus', '.auth-code input', (function(event) {
		if ($(this).val().length > 0) {
			$(this).select();
		}
	}));

	$('body').on('keydown','.auth-code input', (function(event) {
		var key = event.which || event.keyCode;
		if (key == 27) {
			$(':focus').blur();
			return true;
		}
		if (!event.shiftKey && !event.altKey && !event.ctrlKey && key >= 48 && key <= 57 || key >= 96 && key <= 105 || key == 190 || key == 188 || key == 109 || key == 110 || key == 8 || key == 9 || key == 116 || key == 35 || key == 36 || key == 37 || key == 39 || key == 46 || key == 45) {
			$('input').removeAttr('style');
			var target = $(event.target);
			var targetId = target.attr('id');
			var targetIdNum = Number(targetId.slice(targetId.length - 1));
			if (key == 8) {
				$('#char' + (targetIdNum - 1).toString()).focus();
				if ( target.val().length != 0) {
					target.val('');
					return false;
				}
			} else  if (key >= 48 && key <= 57 || key >= 96 && key <= 105) {
				target.val(event.key);
				$('#char' + (targetIdNum + 1).toString()).focus();
				if (authCheck()) {
					$('input').prop('disabled', 'true')
				}
				return false;
			}
			if (key == 37) {
				$('#char' + (targetIdNum - 1).toString()).focus()
			} else if (key == 39) {
				$('#char' + (targetIdNum + 1).toString()).focus();
			}
			return true;
		}
		return false;
	}));
}

function authCheck (jQuery) {
	var authFlag = true;
	$('.auth-code .wrapper').children().each(function(index, el) {
		if ($(this).val().length == 0) {
			authFlag = false;
			return false;
		}
	});
	if (authFlag) {
		authComplete();
	}
	return authFlag;
}

function loadCaption (jQuery) {
	if (!$('.status').length) {
		$(document).off();
		$('body').off();
		document.title = history.state.event.title;
		timer = setTimeout(function() {$('.wrapper#loader').show();}, 1000);
		$.ajax({
			url: "caption.php",
			context: document.body,
			dataType: 'html'
		}).done(function(html) {
			//загрузка CAPTION
			clearTimeout(timer);
			$('.wrapper#loader').hide();
			$(this).append(html);
			$('.header h1').html(history.state.event.title);
			changeStatus('available')
			timer = setTimeout(function() {$('.header').css('top', '-65px'); $('body').css('padding-top','5px');}, 4000);
			$('html, body').scrollTop($(document).height());
			showMessages();
		});
	}
}

function showMessages() {
	$(history.state.messages).each(function(index, el) {
		newLabel(el);
	});
	updateMessages();
}

function authComplete (jQuery) {
	timer = setTimeout(function() {$('.wrapper#loader').show();}, 1000);
	//отправка сообщения
	$.post('auth.php', {char0: $("#char0").val(), char1: $("#char1").val(), char2: $("#char2").val(), char3: $("#char3").val(), char4: $("#char4").val(), char5: $("#char5").val()}, function(data, textStatus, xhr) {
		clearTimeout(timer);
		$('.wrapper#loader').hide();
		var obj = JSON.parse(data);
		if (obj.status == '2') {
			$('.wrapper#loader').show();
			eventIdentificator = obj.data;
			sendRequest(2, 'getCurrentState', null, function(data) {
				$('.wrapper#loader').hide();
				$('.auth-code').remove();
				window.history.pushState(data.data, data.data.event.title, "/"+data.data.event.id);
				document.title = data.data.event.title;
				loadCaption();
				console.log(data);
			},null);
		}
		$('input').css('background-color','#FCBFBD');
		$('input').val('');
		$('input').prop('disabled',null);
		$('#char0').focus();
		
	});
}

function getEventIdFromURL() {
	var pattern = /(\w){7}/;
	var path = document.location.pathname;
	var id = null;
	if (path.match(pattern)) {
		id = path.match(pattern)[0];
	}
	return id;
}

function changeStatus(status) {
	switch (status) {
		case 'available':
		$('.status').attr('id','available');
		$('.status .tip').html('Подключение установлено');
		break;
		case 'partially':
		$('.status').attr('id','partially');
		$('.status .tip').html('Подключение потеряно');
		break;
		case 'unavailable':
		$('.status').attr('id','unavailable');
		$('.status .tip').html('Ошибка подключения');
		break;
		default:
		$('.status').attr('id','none');
		$('.status .tip').html('Мероприятие не началось');
		break;
	}
}

function lostConnection() {
	if ($('.status').attr('id') == 'available') {
		changeStatus('partially');
		return;
	}
	changeStatus('unavailable');
}

function updateMessages() {
	sendRequest(2, 'listenUpdates', { mTimeStamp : history.state.event.mTimeStamp }, function(json) {
		if (history.state) {
			changeStatus('available');
			if (json.data) {
			//добавить парсер
			json.data.messages.forEach(function(el, index) {
				//console.log(el);
				var newHistoryState = history.state;
				var mTimeStamp = parseInt(el.mTimeStamp);
				if (mTimeStamp > parseInt(newHistoryState.event.mTimeStamp)) {
					newHistoryState.event.mTimeStamp = el.mTimeStamp;
				}
				if (el.deleted == 1) {
					newHistoryState.messages = newHistoryState.messages.filter(function (filterElement) {
						if (filterElement.timeStamp == el.timeStamp) 
							{ return false;}
						return true; 
					});
					deleteLabel(el);
					return;
				}
				delete(el.mTimeStamp);
				delete(el.deleted);
				newLabel(el);
				var existMsg = newHistoryState.messages.find(function(element) {
					if (element.timeStamp == el.timeStamp) {
						return true;
					}
					return false;
				});
				if (existMsg) {
					existMsg.text = el.text;
				} else {
					newHistoryState.messages.push(el);
				}
				history.replaceState(newHistoryState, newHistoryState.event.title);
				$('html, body').scrollTop($(document).height());
			});
		}
		updateMessages();
	}
}, function(json) {
	lostConnection();
	if (history.state) {
		updateMessages();
	}
});
}

function newLabel(obj) {
	var timeStamp = new Date(parseInt(obj.timeStamp));
	timeStamp.getHoursMinutes = function() {
		var result = (this.getHours().toString().length > 1) ? this.getHours() : '0' + this.getHours();
		return ''.concat(result,':',(this.getMinutes().toString().length > 1) ? this.getMinutes() : '0'+ this.getMinutes());
	}
	var label = $('#'+timeStamp.getTime());
	if (label.length > 0) {
		if (editLabel(obj)) {
			return true;
		}
		return false;
	} else {
		$('body').append('<div class="caption label" id="'+timeStamp.getTime()+'"><div class="timeStamp">'+timeStamp.getHoursMinutes()+'</div><div class="text">'+obj.text+'</div></div>');
		$('html, body').scrollTop($(document).height());
		return true;
	}
	return false;
}

function editLabel(obj) {
	var label = $('#'+obj.timeStamp);
	if (label.length > 0) {
		$(label).children('.text').html(obj.text);
		return true;
	}
	return false;
}
 
function deleteLabel(obj) {
	var label = $('#'+obj.timeStamp);
	if (label.length > 0) {
		$(label).remove();
		return true;
	}
	return false;
}