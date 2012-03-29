(function($){
	$.fn.ga_event_click = function(settings){
		var config = {
				'classEventMap' : {
					'internalLinkTrack' : {'category':'internal-link','action':'click','opt_label':'URL: ###LINKTEXT###','opt_value':'1'},
					'externalLinkTrack' : {'category':'external-link','action':'click','opt_label':'URL: ###LINKTEXT###','opt_value':'1'},
					'internalFileTrack' : {'category':'internal-file','action':'click','opt_label':'URL: ###LINKTEXT###','opt_value':'1'}
				}
			};

		if(settings) $.extend(config, settings);

		var methods = {
			trackEvent : function(event){
				element = $(this);
				$.each(config.classEventMap, function(k,v){
					if(element.hasClass(k)){
						var linktext = element.attr('href');
						var optlabel = config.classEventMap[k]['opt_label'];
						optlabel = optlabel.replace('###LINKTEXT###',linktext);
						_gaq.push(['_trackEvent', config.classEventMap[k]['category'], config.classEventMap[k]['action'], optlabel,config.classEventMap[k]['opt_value']]);
					}
				});
			}
		}

		this.each(function(){
			$(this).bind('click',methods.trackEvent);
		});

		return this;
	};

})(jQuery);

jQuery(document).ready(function(){
	if(classEventMap) var settings = classEventMap;
	jQuery('a').ga_event_click(settings);
});