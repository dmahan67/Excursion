simpleCart({

	cartColumns: [

		{ view: function(item, column){
				return "<span>"+item.get('quantity')+"</span>";
		}, attr: 'custom' },

		{ attr: "name" , label: false },

		{ view: 'currency', attr: "total" , label: false  },
		
		{ view: function(item, column){
			return	"<span class='item-view'><a href='page.php?id="+item.get('number')+"'>view</a></span>";
		}, attr: 'custom' }
	],
	cartStyle: 'div'
});