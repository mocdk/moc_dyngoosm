Ext.onReady(function(){
    Ext.get('typo3-docheader-row2').select('.docheader-row2-left').createChild({
	tag: 'div',
	cls: 'mdgsm_beseo',
	id: 'mdgsm_beseo_wrap',
	html: mdgsm_html
    });
    Ext.get('mdgsm_beseo_wrap').hover(function(){Ext.get('mdgsm_list').show();},function(){Ext.get('mdgsm_list').hide();});
    //Ext.get('mdgsm_list').select('li').on('click', function(){this.select('input').focus();this.select('li').select();});
});


