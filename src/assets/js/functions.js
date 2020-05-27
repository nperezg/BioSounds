document.addEventListener("DOMContentLoaded", function() {

	if (error) {
		showAlert(error);
	}

	$('#loginForm').on('show.bs.collapse', function () {
		let alertBox = document.getElementById('alertBox');
		if (!alertBox) {
			return;
		}
		alertBox.classList.remove('show');
	});

	document.addEventListener('submit', (event) => {
		if (event.target.matches('.js-async-form')) {
			event.preventDefault();
			postRequest(event.target.action, $(event.target).serialize(), true);
		}
	});

	$(".js-open-modal").click(function(e) {
		let data = [];
		if (this.dataset.id) {
			data = {'id': this.dataset.id};
		}
		requestModal(this.href, data);
		e.preventDefault();		
	});	
	
	$("[data-hide]").on("click", function(){
		$(this).closest("." + $(this).attr("data-hide")).hide();
	});

	$(".log").click(function(){
		$("#alertBox").removeClass('show');
	});
	
	$(".user").click(function(){
		$("#alertBox").removeClass('show');
	});

	toggleLoading();
	 
	 $.fn.toggleDisabled = function(){
        return this.each(function(){
            this.disabled = !this.disabled;
        });
    };

	$(document).on('keydown.autocomplete', '.js-species-autocomplete', function() {
		$(this).autocomplete ({
			source: function (request, response) {
				$.post(baseUrl + '/species/getList', {term: request.term})
					.done(function (data) {
						response(JSON.parse(data));
					})
					.fail(function (response) {
						showAlert(JSON.parse(response.responseText).message);
						response(null);
					});
			},
			minLength: 3,
			change: function (event, ui) {
				if (!ui.item) {
					$(this).val('');
					let type = $(this).data('type');
					$('.js-species-id[data-type=' + type + ']').val('');
					//$('#reviewSpeciesId').val('');
				}
			},
			select: function (e, ui) {
				$(this).val(ui.item.label.split('(')[0]);
				let type = $(this).data('type');
				$('.js-species-id[data-type=' + type + ']').val(ui.item.value);
				// $('#reviewSpeciesId').val(ui.item.value);
				e.preventDefault();
			}
		});
	});
});

function showAlert(message) {
	//TODO: Move hide login form to another place
	$('#loginForm').collapse('hide');

	let alertDiv = document.getElementById('alertBox');

	if (alertDiv) {
		alertDiv.getElementsByTagName('p')[0].textContent = message;
		return;
	}

	alertDiv = document.createElement('div');
	alertDiv.id = 'alertBox';
	alertDiv.classList.add('alert', 'alert-dismissible', 'alert-secondary', 'fade', 'show');
	alertDiv.setAttribute('role', 'alert');

	let paragraph = document.createElement('p');
	paragraph.textContent = message;

	let button = document.createElement('button');
	button.type = 'button';
	button.className = 'close';
	button.setAttribute('data-dismiss', 'alert');
	button.setAttribute('aria-label', 'close');

	let span = document.createElement('span');
	span.innerHTML = '&times;';
	button.appendChild(span);

	alertDiv.appendChild(paragraph);
	alertDiv.appendChild(button);

	let header = document.getElementsByTagName('header')[0];
	document.body.insertBefore(alertDiv, header);
}

function toggleLoading() {
	$('.loading').toggle();
}

function requestModal(href, data = [])
{
	postRequest(href, data, false, false, function (response) {
		$('#modalWindows').html(response.data);
		$("#modal-div").modal('show');
	});
}

function postRequest(href, data = [], showMessage = false, showLoading = false, callback)
{
	asyncRequest('POST', href, data, showMessage, showLoading, callback);
}

function deleteRequest(href, data = [], showMessage = false, showLoading = false, callback)
{
	asyncRequest('DELETE', href, data, showMessage, showLoading, callback);
}

function asyncRequest(type, href, data = [], showMessage = false, showLoading = false, callback) {
	if (showLoading) {
		toggleLoading();
	}

	$.ajax({
	    type: type,
	    url: href,
		data: data,
		dataType: 'json',
	})
	.done(function(response) {
		if (showMessage) {
			showAlert(response.message);
		}
		if (callback && typeof callback === 'function') {
			callback(response);
		}
	})
	.fail(function(response) {
		if (response.status === 401) {
			//showAlert('You are not authenticated. Please, log in.');
			//window.location.href = baseUrl;
		}

		if (!response.responseJSON) {
			showAlert('Error ' + response.message);
			return;
		}
		showAlert(response.responseJSON.message);
	})
	.always(function() {
		if (showLoading) {
			toggleLoading();
		}
	});
}

function getCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');

    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}

/*
 * Function for saving the fields of a list with forms.
 */
function saveFormList(element, url)
{
	let row = element.closest("tr");
	let columns = row.find("input, select");
	let values = {};
	let value = '';

	columns.each(function(i, item) {
		value = item.value;
		if (item.type === "checkbox" && item.checked) {
			value = 1;
		} else if(item.type === "checkbox" && !item.checked) {
			value = 0;
		}
		
		values[item.name+"_"+item.type] = value;
	});

	postRequest(baseUrl + '/' + url, values, false);
}
