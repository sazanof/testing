		var lastImageCtrl;
		var lastFileCtrl;
		function OpenServerBrowser(url, width, height ) 
		{
			var iLeft = (screen.width  - width) / 2 ;
			var iTop  = (screen.height - height) / 2 ;
			var sOptions = \'toolbar=no,status=no,resizable=yes,dependent=yes\' ;
			sOptions += \',width=\' + width ;
			sOptions += \',height=\' + height ;
			sOptions += \',left=\' + iLeft ;
			sOptions += \',top=\' + iTop ;
			var oWindow = window.open( url, \'FCKBrowseWindow\', sOptions ) ;
		}			
		function BrowseServer(ctrl) 
		{
			lastImageCtrl = ctrl;
			var w = screen.width * 0.7;
			var h = screen.height * 0.7;
			OpenServerBrowser(\'/manager/media/browser/mcpuk/browser.html?Type=images&Connector=/manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=/\', w, h);
		}
		function BrowseFileServer(ctrl) 
		{
			lastFileCtrl = ctrl;
			var w = screen.width * 0.7;
			var h = screen.height * 0.7;
			OpenServerBrowser(\'/manager/media/browser/mcpuk/browser.html?Type=files&Connector=/manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=/\', w, h);
		}
		function SetUrl(url, width, height, alt)
		{
			if(lastFileCtrl) 
			{
				var c = document.mutate[lastFileCtrl];
				if(c) c.value = url;
				lastFileCtrl = \'\';
			} 
			else if(lastImageCtrl) 
			{
				var c = document.mutate[lastImageCtrl];
				if(c) c.value = url;
				lastImageCtrl = \'\';
				var val = c.value;
				var getID = document.getElementById(c.name).value;
				$("#span_" + document.getElementById(c.name).name).html(\'<img src="/\' + val + \'" width="40" class="img_vars">\');																								
			} 
			else 
			{
				return;
			}
		}							

	$(document).ready(function()
	{
		var i =  document.getElementById(\'inputs\').getElementsByClassName(\'field\').length;
		$(\'#add\').click(function() 
		{
			i++;
			$(\'\<div class="field" id="files"><span class="overflow" id="span_img_vars\' + i + \'"><img src="/assets/modules/testing/images/50x50.gif" class="img_vars" width="110"></span><div class="float_inf"><b>Вариант ответа №\' + i + \':</b><br><input type="text"  name="question[]" placeholder="Введите вариант ответа №\' + i + \'" /> - <input type="text"  name="ves[]" placeholder="Балл за выбор этого ответа" style="width:30px !important"/><br><input id="img_vars\' + i + \'" name="img_vars\' + i + \'" value="" placeholder="Перед тем, как добывить изображения, сохраните документ" type="text" disabled></div><div class="clear"></div></div>\').fadeIn(\'slow\').appendTo(\'.inputs\');
		});
		$(\'#remove\').click(function() 
		{
			if(i > 1) {
				$(\'.field:last\').remove();
				i--; 
			}
		});
		$(\'#reset\').click(function() 
		{
			while(i > 1) {
				$(\'.field:last\').remove();
				i--;
			}
		});
		$(\'.submit\').click(function()
		{
			var answers = [];
			$.each($(\'.field\'), function() 
			{
				answers.push($(this).val()); 
			});
			if(answers.length == 0) 
			{ 
				answers = "none"; 
			}   
			alert(answers);
			return false;
		});
	});