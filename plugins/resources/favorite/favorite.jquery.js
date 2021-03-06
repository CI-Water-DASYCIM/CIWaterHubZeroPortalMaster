/**
 * @package     hubzero-cms
 * @file        plugins/resources/favorite/favorite.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

//-----------------------------------------------------------
//  Ensure we have our namespace
//-----------------------------------------------------------
if (!HUB) {
	var HUB = {};
}
if (!HUB.Plugins) {
	HUB.Plugins = {};
}

/*
USAGE:

	$.growl(title, msg);
	$.growl(title, msg, image);
	$.growl(title, msg, image, priority);

THEME/SKIN:

You can override the default look and feel by updating these objects:
$.growl.settings.displayTimeout = 4000;
$.growl.settings.noticeTemplate = ''
  + '<div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalTop.png); position: relative; width: 259px; height: 16px; margin: 0pt;"></div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalBackground.png); position: relative; display: block; color: #ffffff; font-family: Arial; font-size: 12px; line-height: 14px; width: 259px; margin: 0pt;">' 
  + '  <img style="margin: 14px; margin-top: 0px; float: left;" src="%image%" />'
  + '  <h3 style="margin: 0pt; margin-left: 77px; padding-bottom: 10px; font-size: 13px;">%title%</h3>'
  + '  <p style="margin: 0pt 14px; margin-left: 77px; font-size: 12px;">%message%</p>'
  + '</div>'
  + '<div style="float: right; background-image: url(my.growlTheme/normalBottom.png); position: relative; width: 259px; height: 16px; margin-bottom: 10px;"></div>'
  + '</div>';
$.growl.settings.noticeCss = {
  position: 'relative'
};

To change the 'dock' look, and position: 

$.growl.settings.dockTemplate = '<div></div>';
$.growl.settings.dockCss = {
    position: 'absolute',
    top: '10px',
    right: '10px',
    width: '300px'
  };
  
The dockCss will allow you to 'dock' the notifications to a specific area
on the page, such as TopRight (the default) or TopLeft, perhaps even in a
smaller area with "overflow: scroll" enabled?
*/

(function($) {
	$.growl = function(title,message,image,priority) { notify(title,message,image,priority); }
	$.growl.version = "1.0.2";

	function create(rebuild) {
		var instance = document.getElementById('growlDock');
		if(!instance || rebuild) {
		  instance = $(jQuery.growl.settings.dockTemplate).attr('id', 'growlDock').addClass('growl');
		  if(jQuery.growl.settings.defaultStylesheet) {
		    $('head').append('<link rel="stylesheet" type="text/css" href="' + jQuery.growl.settings.defaultStylesheet + '" />');
		  }

		} else {
		  instance = $(instance);
		}
		$('body').append(instance.css(jQuery.growl.settings.dockCss));
		return instance;
	};

	function r(text, expr, val) {
		while(expr.test(text)) {
		text = text.replace(expr, val);
		}
		return text;
	};

	function notify(title,message,image,priority) {
		var instance = create();
		var html = jQuery.growl.settings.noticeTemplate;
		if(typeof(html) == 'object') html = $(html).html();
		html = r(html, /%message%/, (message?message:''));
		html = r(html, /%title%/, (title?title:''));
		html = r(html, /%image%/, (image?image:jQuery.growl.settings.defaultImage));
		html = r(html, /%priority%/, (priority?priority:'normal'));

		var notice = $(html)
			.hide()
			.css(jQuery.growl.settings.noticeCss)
			.fadeIn(jQuery.growl.settings.notice);;

		$.growl.settings.noticeDisplay(notice);
		instance.append(notice);
		$('a[rel="close"]', notice).click(function() {
			notice.remove();
		});
		if ($.growl.settings.displayTimeout > 0) {
			setTimeout(function(){
				jQuery.growl.settings.noticeRemove(notice, function(){
					notice.remove();
				});
			}, jQuery.growl.settings.displayTimeout);
		}
	};


	// default settings
	$.growl.settings = {
		dockTemplate: '<div></div>',
		dockCss: {
			position: 'fixed',
			top: '30%',
			right: '45%',
			width: '211px',
			zIndex: 50000
		},
		noticeTemplate: 
			'<div class="notice" style="text-align:center;-webkit-border-radius:12px;-moz-border-radius:12px;border-radius:12px;min-height: 146px;padding:15px;">' +
			' <p><img src="%image%" alt="" /></p>' +
			' <h3 style="margin-top: 15px; color: #fff;">%title%</h3>' +
			'</div>',
		noticeCss: {
			opacity: .75,
			backgroundColor: '#333333',
			color: '#ffffff'
		},
		noticeDisplay: function(notice) {
			notice.css({'opacity':'0'}).fadeIn(jQuery.growl.settings.noticeFadeTimeout);
		},
		noticeRemove: function(notice, callback) {
			notice.animate({opacity: '0', height: '0px'}, {duration:jQuery.growl.settings.noticeFadeTimeout, complete: callback});
		},
		noticeFadeTimeout: 'slow',
		displayTimeout: 1500,
		defaultImage: 'growl.jpg',
		defaultStylesheet: null,
		noticeElement: function(el) {
			$.growl.settings.noticeTemplate = $(el);
		}
	};
})(jQuery);

//----------------------------------------------------------
// Resource Ranking pop-ups
//----------------------------------------------------------
if (!jq) {
	var jq = $;
}

HUB.Plugins.ResourcesFavorite = {
	jQuery: jq,
	
	initialize: function() {
		var $ = this.jQuery;
		
		// Add to favorites
		var fav = $('#fav-this');
		if ($(fav)) {
			$(fav).bind('click', function(e) {
				e.preventDefault();
				
				var rid = $('#rid').val();
				$.get('index.php?option=com_resources&task=plugin&trigger=onResourcesFavorite&no_html=1&rid='+rid, {}, function(data) {
					$('#fav-this').html(data);
					
					if ($(fav).hasClass('faved')) {
						$(fav).removeClass('faved');
						var img = '/components/com_resources/images/broken-heart.gif';
						var txt = 'Favorite removed.';
					} else {
						fav.addClass('faved');
						var img = '/components/com_resources/images/heart.gif';
						var txt = 'Favorite saved.';
					}
					$.growl(txt, '', img);
				});
			});
		}
	} // end initialize
}

jQuery(document).ready(function($){
	HUB.Plugins.ResourcesFavorite.initialize();
});

