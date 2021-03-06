﻿package charts {
	import charts.Elements.Element;
	import charts.Elements.PointBarOutline;
	import string.Utils;
	
	public class BarOutline extends BarBase {
		private var outline_colour:Number;
		
		public function BarOutline( json:Object, group:Number ) {
			
			//
			// specific value for outline
			//
			var style:Object = {
				'outline-colour':	"#000000"
			};
			
			object_helper.merge_2( json, style );
			
			super( json, group );
		}
		
		//
		// called from the base object
		//
		protected override function get_element( index:Number, value:Object ): Element {
			
			var default_style:Object = {
					colour:				this.style.colour,
					'outline-colour':	this.style['outline-colour'],
					tip:				this.style.tip
			};
			
			if( value is Number )
				default_style.top = value;
			else
				object_helper.merge_2( value, default_style );
				
			// our parent colour is a number, but
			// we may have our own colour:
			if( default_style.colour is String )
				default_style.colour = Utils.get_colour( default_style.colour );
			
			if( default_style['outline-colour'] is String )
				default_style['outline-colour'] = Utils.get_colour( default_style['outline-colour'] );
				
			return new PointBarOutline( index, default_style, this.group );
		}
	}
}