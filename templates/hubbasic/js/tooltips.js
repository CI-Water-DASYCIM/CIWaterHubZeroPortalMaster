/**
 * @package     hubzero-cms
 * @file        templates/hubbasic/js/tooltips.js
 * @copyright   Copyright 2005-2011 Purdue University. All rights reserved.
 * @license     http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

/*
Script: MooTips.js
	Tooltips, BubbleTips, whatever they are, they will appear on mouseover

License:
	MIT-style license.

Credits:
	The idea behind Tips.js is based on Bubble Tooltips (<http://web-graphics.com/mtarchive/001717.php>) by Alessandro Fulcitiniti <http://web-graphics.com>
	MooTips.js is based on Tips.js.
		Modified by razvan@e-magine.ro (<http://www.e-magine.ro/>): 
			allow AJAX and DOM usage
		Modified by Vladimir Prieto (<http://vladimirprieto.blogspot.com>): 
			prevent "fixed" tips from hiding onmouseenter of the tip
		Modified by uhleeka@gmail.com:
			allow EVAL usage

NOTE:
	Some unused functionality removed to save on file size
*/

var MooTips = new Class({

	options: { // modded for X3
		onShow: function(tip){
			tip.setStyle('visibility', 'visible');
		},
		onHide: function(tip){
			tip.setStyle('visibility', 'hidden');
		},
		showOnClick: false,
		showOnMouseEnter: true,
		maxTitleChars: 30,
		showDelay: 100,
		hideDelay: 100,
		className: 'tool',
		offsets: {'x': 16, 'y': 16},
		fixed: false,
		loadingText: 'Loading...',
		errTitle: 'Error...',
		errText: 'There was a problem retrieving the contents of this tooltip.',
		evalAlways: false
	},

	initialize: function(elements, options){
		this.setOptions(options);
		this.toolTip = new Element('div', {
			'class': this.options.className + '-tip',
			'styles': {
				'position': 'absolute',
				'top': '0',
				'left': '0',
				'visibility': 'hidden'
			},
			'events': {
					'mouseenter': function(event){
						//setting state property, needed on end function
						this.setProperty('state','mouseenter');
					},
					'mouseleave': function(event){
						//setting state property, needed on end function
						this.setProperty('state','mouseleave');
						this.pather.end(event);
					}
			}
		}).inject(document.body);
		
		//didn't find other way to get owner of toolTip inside toolTip
		this.toolTip.pather = this;
			
		this.wrapper = new Element('div').inject(this.toolTip);
		$$(elements).each(this.buildEvents, this);
		$$(elements).each(this.build, this);
		if (this.options.initialize) this.options.initialize.call(this);
	},
	
	buildEvents: function(el) {
		//code with errors but works
		//that's why showOnClick option is false by default
		if (this.options.showOnClick) {
			el.addEvent('click', function(event){
				this.start(el);
				if (!this.options.fixed) this.locate(event);
				else this.position(el);
			}.bindWithEvent(this));
		}
		
		if (this.options.showOnMouseEnter) {
			el.addEvent('mouseenter', function(event){
				this.start(el);
				if (!this.options.fixed) this.locate(event);
				else this.position(el);
			}.bind(this));
		}
		
		if (!this.options.fixed) el.addEvent('mousemove', this.locate.bindWithEvent(this));
		var end = this.end.bind(this);
		el.addEvent('mouseleave', end);
		el.addEvent('trash', end);
	},

	build: function(el){ // modded for X3
		el.$tmp.myTitle = (el.href && el.getTag() == 'a') ? el.href.replace('http://', '') : (el.rel || false);
		if (el.title){
			
			if (el.title.test('^DOM:', 'i')) { // check if we need to extract contents from a DOM element
				el.title = $(el.title.split(':')[1].trim()).innerHTML;
			}
								
			var dual = el.title.split('::');
			if (dual.length > 1) {
				el.$tmp.myTitle = dual[0].trim();
				el.$tmp.myText = dual[1].trim();
			} else {
				el.$tmp.myTitle = false;
				el.$tmp.myText = el.title;
			}					
			el.removeAttribute('title');
		} else {
			el.$tmp.myText = false;
		}
		if (el.$tmp.myTitle && el.$tmp.myTitle.length > this.options.maxTitleChars) el.$tmp.myTitle = el.$tmp.myTitle.substr(0, this.options.maxTitleChars - 1) + "&hellip;";
	},

	start: function(el){ // modded for X3
		this.wrapper.empty();
	
		if (el.$tmp.myTitle){
			this.title = new Element('span').inject(
				new Element('div', {'class': this.options.className + '-title'}).inject(this.wrapper)
			).setHTML(el.$tmp.myTitle);
		}
		if (el.$tmp.myText){
			this.text = new Element('span').inject(
				new Element('div', {'class': this.options.className + '-text'}).inject(this.wrapper)
			).setHTML(el.$tmp.myText);
			
			if((this.options.evalAlways) && (el.$tmp.myEvalAlwaysText)) {
				// reset text so that it will evaluate again
				el.$tmp.myText = el.$tmp.myEvalAlwaysText;
			}
		}
		$clear(this.timer);
		
		// setting initial state of tip
		this.toolTip.setProperty('state','mouseleave');
		
		this.timer = this.show.delay(this.options.showDelay, this);
	},

	end: function(event){
		$clear(this.timer);
		this.timer = this.hide.delay(this.options.hideDelay, this);
	},

	position: function(element){
		var pos = element.getPosition();
		this.toolTip.setStyles({
			'left': pos.x + this.options.offsets.x,
			'top': pos.y + this.options.offsets.y
		});
	},

	locate: function(event){
		var win = {'x': window.getWidth(), 'y': window.getHeight()};
		var scroll = {'x': window.getScrollLeft(), 'y': window.getScrollTop()};
		var tip = {'x': this.toolTip.offsetWidth, 'y': this.toolTip.offsetHeight};
		var prop = {'x': 'left', 'y': 'top'};
		for (var z in prop){
			var pos = event.page[z] + this.options.offsets[z];
			if ((pos + tip[z] - scroll[z]) > win[z]) pos = event.page[z] - this.options.offsets[z] - tip[z];
			this.toolTip.setStyle(prop[z], pos);
		};
	},

	show: function(){
		if (this.options.timeout) this.timer = this.hide.delay(this.options.timeout, this);
		this.fireEvent('onShow', [this.toolTip]);
	},

	hide: function(){
		// if "fixed", tooltip is only hidden when mouse leaves the tooltip (itself)
		if ((this.toolTip.getProperty('state') == 'mouseleave') || (!this.options.fixed))
			this.fireEvent('onHide', [this.toolTip]);
	}
});

MooTips.implement(new Events, new Options);
