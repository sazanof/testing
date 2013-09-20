<?php
/*
# MODX TESTING MODULE
# AUTHOR SAZANOF siuzi_drum@mail.ru
# VERSION 1.b3
# --.--.2013

*/

$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$mod_table = $dbprefix."test_questions"; //таблица модуля
$mod_table_th = $dbprefix."test_themes"; //таблица модуля
$tbl_results = $modx->getFullTableName('test_results');
$tbl_results_balls = $dbprefix."test_results_balls";
$start_table = $dbprefix."test_started";
$theme = $modx->config['manager_theme']; //тема админки
$basePath = $modx->config['base_path']; //путь до сайта на сервере

$m_url = $modx->config['site_url'].'manager/index.php?a=112&id='.(int)$_REQUEST['id'];

// путь до папки с модулем (с урлом)
$path = $modx->config['site_url'].'assets/modules/testing/';
// путь до папки с модулем (для файлов)
$mod_path = MODX_BASE_PATH.'assets/modules/testing/';

$title = $title ? $title : 'Список тестов';
$out ='';
if(IN_MANAGER_MODE=="true")
{
	switch ($_GET['action'])
	{
		case 'install' :
		$title = 'Установка модуля';		
		$sql = "CREATE TABLE $mod_table (id INT(11) NOT NULL AUTO_INCREMENT, id_theme INT(11),question VARCHAR(255),img VARCHAR(255), json_answ TEXT, correct_answ INT(2), PRIMARY KEY (id))";
		$modx->db->query($sql);
		$sql2 = "CREATE TABLE IF NOT EXISTS $mod_table_th ( `id` int(11) NOT NULL AUTO_INCREMENT, `title` varchar(255) NOT NULL, `description` TEXT NOT NULL, `test_type` INT NOT NULL, PRIMARY KEY (`id`)) ";
		$modx->db->query($sql2);		
		$sql3="CREATE TABLE $start_table (`id` INT(9) NOT NULL AUTO_INCREMENT ,`session_id` VARCHAR(255) NOT NULL ,`time_death` VARCHAR(255) NOT NULL ,`questions_id` TEXT NOT NULL ,`answers` TEXT NOT NULL, `num` INT(9),PRIMARY KEY ( `id` )) ";
		$modx->db->query($sql3);
		$sql4 = "
				CREATE TABLE IF NOT EXISTS $tbl_results (
				`id` INT NOT NULL AUTO_INCREMENT ,
				`id_test` INT NOT NULL ,
				`min` INT( 9 ) NOT NULL ,
				`max` INT( 9 ) NOT NULL ,
				`res` TEXT NOT NULL ,
				PRIMARY KEY ( `id` )
				);";
		$modx->db->query($sql4);
		$sql5 = "
						CREATE TABLE IF NOT EXISTS $tbl_results_balls (
						`id` INT NOT NULL AUTO_INCREMENT ,
						`test_id` INT NOT NULL ,
						`title` VARCHAR( 255 ) NOT NULL ,
						`descr` TEXT NOT NULL ,
						PRIMARY KEY ( `id` )
						)";
		$modx->db->query($sql5);
		break;
		case 'edit_questions':
		$title = 'Список вопросов теста';
		break;
		case 'edit_theme' :
		$title = 'Редактирование теста (темы)';
		break;
		case 'add_q' :
		$title = 'Вопрос и варианты ответов';
		break;
		case 'add_theme' :
		$title = 'Добавление нового теста (новой темы)';
		break;
		case 'help' :
		$title = 'Раздел помощи';
		break;
	}
	$link ='<a class="docs" href="'.$m_url.'&action=help">Раздел помощи</a>';
	if($_GET['action'] == 'help') $link = '<a class="docs" href="'.$m_url.'">Закрыть помощь</a>';
	
	$head='
	<!DOCTYPE html> 
	<head>
	<title>Модуль тестирования для MODx Evolution</title>
	<link rel="stylesheet" type="text/css" href="media/style/'.$theme.'/style.css">
	<link rel="stylesheet" type="text/css" href="/assets/modules/testing/css/module.css">
	<script type="text/javascript" src="/assets/modules/testing/js/jquery.js"></script>
	<script type="text/javascript">
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
</script>
<script>
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
</script>
	</head>
	<body>
	<h1>Модуль тестирования для MODx Evolution</h1>
	<div class="sectionHeader">'.$title.$link.'</div>
	<div class="sectionBody">';
	if ($modx->db->getRecordCount($modx->db->query("SHOW TABLES FROM  $dbname LIKE '$mod_table'")) ==0) 
	{
		// Если таблица не существует выводим окно установки
		$out .= '<div class="intro">
			<img src="'.$path.'images/logotype.png" style="display:block; margin:0 auto">
			<div class="text-intro"><b>MODX™ Testing</b> - это бесплатный модуль тестирования для CMS | CMF MODx Evolution.
			<br>
				<ul class="actionButtons">
					<li>
						<a href="'.$m_url.'&action=install"><img src="media/style/MODxCarbon/images/icons/table.gif"> Установить модуль</a>
					</li>
				</ul>
				<small>Вопросы, помощь в доработке принимаются по адресу <a target="_blank" href="mailto:siuzi_drum@mail.ru">siuzi_drum@mail.ru</a></small>

			</div>
		</div>';
	}
	else
	{
	if ($_GET['action'] =='add_theme')
		{
			if($_POST['add_questions'])
			{
				$sql = "INSERT INTO $mod_table_th VALUES ('NULL','".$modx->db->escape($_POST['theme_title'])."','".$modx->db->escape($_POST['theme_description'])."','".$modx->db->escape($_POST['test_type'])."')";
				echo $sql;
				$modx->db->query($sql);
				header("Location: index.php?a=112&id=".$_REQUEST['id']." ");

			}
			else
			{
				$form = '
				<fieldset><legend>Создание новой темы</legend>
				<div class="info">
				<img src="media/style/MODxCarbon/images/icons/information.png" style="float:left; margin-right:6px"> 
				<span>Заполните поля ниже. После создания новой темя тестирования вы будете переадресованы на страницу добавления вопросов.</span>
				</div>
				<form method="post" id="add_theme" action="">
				<input name="theme_title" type="text" value="" placeholder="Введите заголовок теста"><br>
				<textarea name="theme_description" placeholder="Введите описание теста"></textarea><br>
				<input type="radio" name="test_type" value="1"> Проверка знаний (баллы начисляются за правильный ответ)<br>
				<input type="radio" name="test_type" value="0"> Тестирование (Результат по сумме набранных баллов)<br>
				<input type="submit" name="add_questions" value="Сохранить" class="but"> <input type="reset" value="Назад" onclick="javascript:history.back();" class="but">
				</form></fieldset>';
				$out .= $form;
				
			}
		}
		elseif ($_GET['action'] == 'edit_theme')
		{
			$sql = "SELECT * FROM $mod_table_th WHERE id = '".(int)$_GET['theme_id']."'";
			$res = $modx->db->getRow($modx->db->query($sql));
			$out .= '<div id="actions"><ul class="actionButtons">
			<li id="Button1">
						<a href="'.$m_url.'&action=edit_theme&theme_id='.(int)$_GET['theme_id'].'&act=results">
							 <img src="media/style/MODxCarbon/images/icons/b06.gif"> Управление результатами 
						</a>
					</li></ul></div>
			';
			if ($_GET['act']=='results')
			{
				if ($_GET['add']=='true')
				{
					$out .= '<form method="post">
					<input type="text" value="'.$_POST['min'].'" name="min" placeholder="Мин. значение баллов"><br> 
					<input type="text" value="'.$_POST['max'].'" name="max" placeholder="Макс. значение баллов"><br>
					<textarea name="result" placeholder="Текст результата"></textarea><br>
					<button type="submit" value="sub" name="sub" class="but">Сохранить</button>
					</form>';
					if ($_POST['sub'])
					{
						if ((int)($_POST['max']) and $_POST['result'] !=='')
						{
							$out .= 'good';
							$sql = "INSERT INTO $tbl_results (id,id_test,min,max,res) VALUES (NULL, '".$modx->db->escape($_GET['theme_id'])."', '".$modx->db->escape($_POST['min'])."','".$modx->db->escape($_POST['max'])."', '".$modx->db->escape($_POST['result'])."')";
							if ($modx->db->query($sql))
							{
								header("Location: index.php?a=112&id=".$_REQUEST['id']."&action=edit_theme&theme_id=".(int)$_GET['theme_id']."&act=results");
							}
						}
						else
						{
							$out .= 'int error! no no no!';
						}
					}
				}
				// выводим рез теста
				else
				{
					//если тип тестирования  без  баллов то:
					$sql = "SELECT * FROM $mod_table_th WHERE id = '".(int)$_GET['theme_id']."'";
					$res = $modx->db->getRow($modx->db->query($sql));
					if ($res['test_type']==0)
					{
						$out .= '
						<br>
						<ul class="actionButtons">
						<li><a href="'.$m_url.'" id="reset"><img src="media/style/MODxCarbon/images/icons/home.gif"> На главную</a></li>';
						$out .= '<li><a href="'.$m_url.'&action=edit_theme&theme_id='.(int)$_GET['theme_id'].'&act=results&add=true" id="reset"><img src="media/style/MODxCarbon/images/icons/add.png"> Добавить вариацию</a></li>';
						$out .= '</ul>';
				
						if ($_GET['ed'])
						{
							$sql = "SELECT * FROM $tbl_results WHERE id='".(int)$_GET['ed']."'";
							$res = $modx->db->query($sql);
							$res = $modx->db->getRow($res);
							$out .= '<form method="post">
							<input type="text" value="'.$res['min'].'" name="min" placeholder="Мин. значение баллов"><br> 
							<input type="text" value="'.$res['max'].'" name="max" placeholder="Макс. значение баллов"><br>
							<textarea name="result" placeholder="Текст результата">'.$res['res'].'</textarea><br>
							<button type="submit" value="sub" name="sub" class="but">Сохранить</button>
							</form>';
							if ($_POST['sub'])
							{
								$sql="UPDATE $tbl_results set
								min = '".$modx->db->escape($_POST['min'])."',
								max = '".$modx->db->escape($_POST['max'])."',
								res = '".$modx->db->escape($_POST['result'])."'
								WHERE id = ".$res['id']."";
								$modx->db->query($sql);
								header("Location:".$_SERVER['REQUEST_URI']);
							}
						}
						elseif ($_GET['del'])
						{
							$sql = "DELETE FROM $tbl_results WHERE id='".(int)$_GET['del']."'";
							$modx->db->query($sql);
							header("Location: index.php?a=112&id=".$_REQUEST['id']."&action=edit_theme&theme_id=".(int)$_GET['theme_id']."&act=results");
						}
						else
						{
							$sql = "SELECT * FROM $tbl_results WHERE id_test='".(int)$_GET['theme_id']."' ORDER BY min ASC";
							$res = $modx->db->query($sql);
							$res = $modx->db->makeArray($res);
							//print_r($res);
							$out .= '<table class="grid">
							<tr>
							<td class="gridHeader">ID</td>
							<td class="gridHeader">Мин. кол-во баллов</td>
							<td class="gridHeader">Макс. кол-во баллов</td>
							<td class="gridHeader">Текст результата</td>
							<td class="gridHeader">Действия</td>
							<tr>';
							foreach ($res as $r)
							{
								$out .= '<tr>';
									$out .= '<td>';
										$out .= $r['id'];
									$out .= '</td>';
									$out .= '<td>';
										$out .= $r['min'];
									$out .= '</td>';
									$out .= '<td>';
										$out .= $r['max'];
									$out .= '</td>';
									$out .= '<td>';
										$out .= $r['res'];
									$out .= '</td>';
									$out .= '<td>';
										$out .= '<a href="'.$m_url.'&action=edit_theme&theme_id='.$r['id_test'].'&act=results&ed='.$r['id'].'">Ред</a> 
										| 
										<a href="'.$m_url.'&action=edit_theme&theme_id='.$_r['id_theme'].'&act=results&del='.$r['id'].'">Уд</a>';
									$out .= '</td>';
								$out .= '</tr>';
							}
							$out .= '</table>';
						}
					}
					else
					{
						$out .= '
						<br>
						<ul class="actionButtons">
						<li><a href="'.$m_url.'" id="reset"><img src="media/style/MODxCarbon/images/icons/home.gif"> На главную</a></li>';
						$out .= '</ul>';
				
						//$out .= 'Создание таблицы с результатами на баллы, вставка в результаты плейсхолдеры, которые генерит сниппет [+total+] и [+balls+], для управления результатами';
						//print_r ($res);
						$sql = "SELECT * FROM $tbl_results_balls WHERE test_id='".(int)$_GET['theme_id']."'";
												
						$edit_res_balls = $modx->db->escape($_GET['edit_res_balls']);
						switch ($modx->db->getRecordCount($modx->db->query($sql)))
						{
							case 1 :
							$res_title_balls = "Редактирование результата тестирования";
							$text = $modx->db->getRow($modx->db->query($sql));
							$title_b = $text['title'];
							$descr_b = $text['descr'];
							$do_b = 'UPDATE';
							//$out .= 'edit';
							break;
							case 0 :
							$res_title_balls = "Добавление результата тестирования";
							$title_b = '';
							$descr_b = 'Вы набрали [+balls+] из [+total+]. Используйте эти плейсхолдеры, не забывайте!';
							//$out .= 'create';
							$do_b = "INSERT";
							break;
						}
						$form = '
						<h2>'.$res_title_balls.'</h2>
						<fieldset>
						<form method="post">
							<b>Введите заголовок результата:</b><br>
							<input type="text" name="res_title" value="'.$title_b.'" placeholder="Введите заголовок результата"><br>
							<b>Введите текст результата:</b><br>
							<textarea name="res_description" placeholder="Текст">'.$descr_b.'</textarea><br>
						
						</fieldset>
							
						<fieldset>
							<button type="submit" name="act_res" value="'.$do_b.'" class="but">Сохранить результат</button>
						</fieldset>
						</form>
						';
						if ($_POST['act_res'] == 'INSERT')
						{
							$sql = "INSERT INTO $tbl_results_balls (id, test_id, title, descr) VALUES (
							NULL,
							'".$modx->db->escape($_GET['theme_id'])."',
							'".$modx->db->escape($_POST['res_title'])."',
							'".$modx->db->escape($_POST['res_description'])."'
							)";
							if($modx->db->query($sql))
							{
								header ("Location:".$_SERVER['REQUEST_URI']);
							}
						}
						elseif ($_POST['act_res'] == 'UPDATE')
						{	
							$sql = "UPDATE $tbl_results_balls SET
							title ='".$modx->db->escape($_POST['res_title'])."',
							descr='".$modx->db->escape($_POST['res_description'])."'
							WHERE test_id='".$modx->db->escape($_GET['theme_id'])."'";
							if($modx->db->query($sql))
							{
								header ("Location:".$_SERVER['REQUEST_URI']);
							}
						}
						$out .= $form;
					}
				}
			}
			else
			{
				if ($res['test_type']==0)
				{
					$test_type_checked = 
					'<input type="radio" name="test_type" value="1">'.$test_type_checked.' Проверка знаний (баллы начисляются за правильный ответ)<br>
					<input type="radio" name="test_type" value="0" checked>'.$test_type_checked.' Тестирование (Результат по сумме набранных баллов)<br>';
				}
				elseif ($res['test_type']==1)
				{
					$test_type_checked = 
					'<input type="radio" name="test_type" value="1" checked>'.$test_type_checked.' Проверка знаний (баллы начисляются за правильный ответ)<br>
					<input type="radio" name="test_type" value="0">'.$test_type_checked.' Тестирование (Результат по сумме набранных баллов)<br>';
				}
				$form = '
				<fieldset><legend>Редактирование темы с ID ='.$_GET['theme_id'].'</legend>
					<form method="post" action="">
					<b>Название темы:</b><br>
					<input name="theme_title" type="text" value="'.$res['title'].'" placeholder="Введите заголовок теста"><br>
					<b>Описание темы:</b><br>
					<textarea name="theme_description" placeholder="Введите описание теста">'.$res['description'].'</textarea>
					</fieldset>
					
					<fieldset>
					'.$test_type_checked.'
					</fieldset>
					
					<fieldset>
					<input type="submit" name="save_post" value="Сохранить изменения" class="but"> <input type="reset" value="Назад" onclick="javascript:history.back();" class="but">
					</form>
				</fieldset>';
				$out .= $form;
				if (($_POST['title']!=='') and ($_POST['description']!=='') and $_POST['save_post'])
				{
					$sql = "UPDATE $mod_table_th SET 
					title='".$modx->db->escape($_POST['theme_title'])."',
					test_type='".$modx->db->escape($_POST['test_type'])."',
					description='".$modx->db->escape($_POST['theme_description'])."' WHERE id = '".$res['id']."'";
					$modx->db->query($sql);
					header("Location: index.php?a=112&id=".$_REQUEST['id']."&theme_id=".(int)$_GET['theme_id']);
				}
			}
			
		}
		elseif ($_GET['action'] == 'edit_questions')
		{
			$sql = "SELECT * FROM $mod_table";
			$res = $modx->db->query($sql);
			if ($modx->db->getRecordCount($res) == 0)
			{
				$out .= '<div class="info">В базе нет ни одного Вопроса</div>';
			}
			else
			{
			
				$sql = "SELECT * FROM $mod_table WHERE id_theme='$_GET[theme_id]' ORDER BY id ASC";
				$res = $modx->db->query($sql);
				$res = $modx->db->makeArray($res);
				$i = 1;
				$out .= '<table class="zebra"><tbody>
				<tr><th colspan="4" style="text-align:center">Список вопросов</th></tr><tfoot><tr><td colspan="4"></td></tr></tfoot>';
				foreach ($res as $val)
				{
					$out .= '<tr>
					<td width="10">'.$i.'</td>
					<td><img src="'.$path.'images/q.png"class="in_q_img"><span class="in_q_text">'.$val['question'].'</span></td>
					<td width="40"><a href="'.$m_url.'&action=add_q&theme_id='.(int)$val['id'].'">Редактировать</a></td>
					<td width="20"><a href="'.$m_url.'&action=del_q&theme_id='.(int)$val['id'].'">Удалить</a></td>
					</tr>';
					$i++;
				}
				$out .= '</tbody></table>';
			}
			$out.= '<br>
			<div id="actions">
			<ul class="actionButtons">
			<li><a href="'.$m_url.'"><img src="media/style/MODxCarbon/images/icons/home.gif"> К списку тем</a></li>
			<li><a href="'.$m_url.'&action=add_q&theme='.$_GET['theme_id'].'"><img src="media/style/MODxCarbon/images/icons/add.png"> Добавить вопрос</a></li>
			<li><a href="javascript:history.back();"><img src="media/style/MODxCarbon/images/icons/prev.gif"> Назад</a></li>
			</ul>
			</div>';
		}
		
		elseif ($_GET['action'] == 'add_q')
		{
			$sql = "SELECT * FROM $mod_table WHERE id='$_GET[theme_id]' ORDER BY id ASC";
			$res = $modx->db->query($sql);
			$res = $modx->db->getRow($res);
				
			$out .= '
			<div id="actions">
			<ul class="actionButtons">
			<li><a href="'.$m_url.'" id="reset"><img src="media/style/MODxCarbon/images/icons/home.gif"> На главную</a></li>
			<li><a href="javascript:history.back()" id="reset"><img src="media/style/MODxCarbon/images/icons/prev.gif"> К списку вопросов</a></li>
			</ul>
			</div>';
####### РЕДАКТИРОВНИЕ ВОПРОСА
			$form = '
				<form method="post" id="add_q" name="mutate" action="">
				<fieldset>
				<legend>Редактирование вопроса</legend>
				<div class="padding">
				<div class="info"><img src="media/style/MODxCarbon/images/icons/information.png" style="float:left; margin-right:6px"> <span>Введите название вопроса. Оно будет отображаться в списке вопросов при прохождении тестирования на Ващем сайте.<br>
				Не забывайте указывать правильный вариант ответа (это цифра, соответствующая номеру правильного варианта ответа в разделе <b>"Редактирование вариантов ответа"</b>)
				</span></div>
				';
				$form .= '<div class="field"><span class="overflow" id="span_img">';
				if (!empty($res['img']))
				{
					$form .= '<img src="/'.$res['img'].'" width="110" class="img_vars" onclick="BrowseServer(\'img\')">';
				}
				else
				{
					$form .= '<img src="/assets/modules/testing/images/50x50.gif" width="110" class="img_vars" onclick="BrowseServer(\'img\')">';
				}
				$form.='</span><div class="float_inf">';
				if ($res)
				{
					$form .= '
					<b>Вопрос</b> <br><input name="q_title" type="text" value="'.$res['question'].'" placeholder="Введите вопрос" style="width:370px">
					|
				<b>Правильный ответ:</b>
				<input name="correct_answer" type="text" maxlength="2" value="'.$res['correct_answ'].'" style="width:30px !important" required><br>';
				}
				else
				{
					$form .='<input name="q_title" type="text" value="" placeholder="Введите вопрос" style="width:250px !important">
					|
				<b>Правильный ответ: </b>
				<input name="correct_answer" type="text" maxlength="2" value="0" style="width:30px !important" required>';
				}
				$form .='
				<br><b>Изображение:</b><br>
				<input id="img" name="img" value="'.$res['img'].'" onchange="documentDirty=true;" type="text" style="width:150px !important">
				<input value="Вставить" onclick="BrowseServer(\'img\')" type="button">
				</div>';
				$form .= '<div class="clear"></div>
				</div></div>
				
				</fieldset><div class="padding"></div>';
			$form .= '<fieldset style="position:relative">
				<legend>Редактирование вариантов ответа</legend>
				<div class="info"><img src="media/style/MODxCarbon/images/icons/information.png" style="float:left; margin-right:6px"> <span>Теперь введите варианты ответа. Вы можете добавлять и удалять сразу несколько. Для этого воспользуйтесь кнопками действий справа.<br>
				Не забывайте указывать <b>количество баллов</b> за выбранный посетителем сайта ответ. Рекомендуется использовать <b>1</b> в качестве правильного ответа и <b>0</b> в качестве неправильного.</span></div>
				';
			$form .= '<div class="inputs" id="inputs">';
			if ($res)
			{
				$j_row = $res['json_answ'];
				$j = array();
				$j = explode (';',$j_row);
				
				$i = 0;
				$imgs = array();
				foreach($j as $val)
				{
					$j_en = json_decode ($j[$i],true);
					$form .= '
					<div class="field">';
					
						$form .='<span class="overflow" id="span_img_vars'.($i+1).'">';
						if (!empty($j_en['imgs']))
							{
								$form .= '<img src="/'.$j_en['imgs'].'" width="110" class="img_vars" onclick="BrowseServer(\'img_vars'.($i+1).'\')">';
							}
						else
						{
							$form .= '<img src="/assets/modules/testing/images/50x50.gif" width="110" class="img_vars" onclick="BrowseServer(\'img_vars'.($i+1).'\')">';
						}
						$form.='</span>';
						
						$form .= '<div class="float_inf">
						<b>Вариант ответа №'.($i+1).':</b><br>
						<input type="text" value="'.$j_en['answer'].'" name="question[]" > - 
						<input type="text" name="ves[]" value="'.$j_en['ball'].'" placeholder="Значение из базы" style="width:30px !important" required>
						<br>
						<input id="img_vars'.($i+1).'" name="img_vars'.($i+1).'" value="'.$j_en['imgs'].'" onchange="documentDirty=true;" type="text" style="width:150px !important">
						<input value="Вставить" onclick="BrowseServer(\'img_vars'.($i+1).'\')" type="button">
						</div>
						';
						
					
					$form .='<div class="clear"></div></div>';
					$imgs[] = $_POST['img_vars'.($i+1)];
					$i++;
				}
			}
			else
			{
				$form .='<div class="field">';
				$form .='<span class="overflow" id="span_img_vars1">';
						if (!empty($j_en['imgs']))
							{
								$form .= '<img src="/'.$j_en['imgs'].'" width="110" class="img_vars" onclick="BrowseServer(\'img_vars1\')">';
							}
						else
						{
							$form .= '<img src="/assets/modules/testing/images/50x50.gif" width="110" class="img_vars" onclick="BrowseServer(\'img_vars1\')">';
						}
						$form.='</span>';
						
						$form .= '<div class="float_inf">
						<b>Вариант ответа №1:</b><br>
						<input type="text" value="'.$j_en['answer'].'" name="question[]" > - 
						<input type="text" name="ves[]" value="'.$j_en['ball'].'" placeholder="Значение из базы" style="width:30px !important" required>
						<br>
						<input id="img_vars1" name="img_vars1" value="'.$j_en['imgs'].'" onchange="documentDirty=true;" type="text" style="width:150px !important">
						<input value="Вставить" onclick="BrowseServer(\'img_vars1\')" type="button">
						</div>
						';
						
					
					$form .='<div class="clear"></div></div>';
			}
			$form .='</div>';
			$form .='
			<ul class="actionButtons" style="position:absolute; right:20px; top:0px">
			<li><a href="javascript:" id="add"><img src="media/style/MODxCarbon/images/icons/save.png"> Добавить вариант</a></li>
			<li><a href="javascript:" id="remove"><img src="media/style/MODxCarbon/images/icons/delete.png"> Удалить вариант</a></li>
			</ul>
			</fieldset><fieldset>';
			if (!$res)
			{
				$form .= '<input type="submit" name="set_vars" value="Сохранить всё и записать" class="but">';
			}
			else
			{
				$form .= '<input type="submit" name="set_vars" value="Сохранить изменения" class="but">';
			}
			$form .= '<input type="reset" value="Назад" onclick="javascript:history.back();" class="but">';
			$form .='</fieldset></form>';
			$out .= $form;
			if ($_POST['set_vars'])
			{
				$json_string = array();
				$ves = $_POST['ves'];
				$question = $_POST['question'];
				$i=0;
				foreach ($ves as $key => $val)
				{
					$arr_j = array
					(
						'id' => $key,
						'answer' => $question[$i],
						'ball' => $val,
						'imgs' => $imgs[$i],
					);
					// получаем варианты ответов в json формате
					$json = preg_replace_callback('/\\\u([0-9a-fA-F]{4})/',
					create_function('$match', 'return mb_convert_encoding("&#" . intval($match[1], 16) . ";", "UTF-8", "HTML-ENTITIES");'),
					json_encode($arr_j));
					$json_string[] = $json;
					//echo $json;
					$i++;
				}
				$q_title = $modx->db->escape($_POST['q_title']);
				$q_title = str_replace('"','&quot;',$q_title);
				$json_string = implode(';',$json_string);
				if (!$res)
				{
					$sql = "INSERT INTO $mod_table VALUES ('NULL','".(int)$_GET['theme']."','".$q_title."','".$modx->db->escape($_POST['img'])."','".$modx->db->escape($json_string)."','".$modx->db->escape((int)$_POST['correct_answer'])."')";
					//echo $sql;
					$modx->db->query($sql);
					header("Location: index.php?a=112&id=".$_REQUEST['id']."&action=edit_questions&theme_id=".$_GET['theme']."");
				}
				else
				{
					$sql = "UPDATE $mod_table SET question = '".$q_title."',img='".$modx->db->escape(trim($_POST['img']))."',json_answ = '".$modx->db->escape($json_string)."',correct_answ = '".$modx->db->escape((int)$_POST['correct_answer'])."' WHERE id = '".(int)$_GET['theme_id']."'";
					//echo $sql;
					$modx->db->query($sql);
					header("Location: ".$_SERVER['REQUEST_URI']);
				}
				
				//echo $sql;
			}
		}
		elseif ($_GET['action'] == 'del_q')
		{
			$out .='
			<fieldset><legend>Удаление вопроса</legend>
			<form method="post" action="" class="margin">
				<div class="info">Вы действительно хотите удалить вопрос? Внимание! Вместе с вопросом удалятся и все варианты ответов! Удаление - необратимый процесс.</div>
				<input type="submit" name="del_this_q" value="Удалить вопрос" class="but">
				<input type="reset" onclick="javascript:history.back()" value="Отменить удаление" class="but">
			</form>
			</fieldset>';
			$sql_q = "DELETE FROM $mod_table WHERE id ='".(int)$_GET['theme_id']."'";
			if ($_POST['del_this_q'])
			{
				$sql="SELECT id_theme FROM $mod_table WHERE id ='".(int)$_GET['theme_id']."' ";
				$res = $modx->db->getRow($modx->db->query($sql));
				$_SESSION['thid'] = $res['id_theme'];
				$modx->db->query($sql_q);
				header("Location: index.php?a=112&id=".$_REQUEST['id']."&action=edit_questions&theme_id=".$_SESSION['thid']." ");
				unset ($_SESSION['thid']);
			}
		}
		
		else
		{
			$sql = "SELECT * FROM $mod_table_th";
			$res = $modx->db->query($sql);
			$res = $modx->db->makeArray($res);
			//print_r ($res);
			$tbl = '';
			if ($_GET['action']=='drop')
			{
				$tbl .= '<fieldset><legend>Удаление модуля и таблиц В БД</legend>
				<div class="info"><img src="media/style/MODxCarbon/images/icons/information.png" style="float:left; margin-right:6px">
				<span>Вы находитесь в режиме удаления модуля. Удаление модуля повлечет за собой и удаление таблиц из базы данных. Вы уверены, что хотите удалить модуль?</span>
				</div>
				<form method="post" action="">
				<input type="submit" name="drop" value="Удалить модуль" class="but">
				<input type="reset" onclick="javascript:history.back()" value="Отменить удаление" class="but">
				</form>
				</fieldset>';
				if ($_POST['drop'])
				{
					$sql = "DROP TABLE $mod_table_th,$mod_table,$start_table,$tbl_results,$tbl_results_balls";
					$modx->db->query($sql);
					header("Location: index.php?a=112&id=".$_REQUEST['id']);
				}
			}
			elseif ($_GET['action']=='truncate')
			{
				$tbl .= '<fieldset><legend>Очистка таблиц в БД</legend>
				<div class="info"><img src="media/style/MODxCarbon/images/icons/information.png" style="float:left; margin-right:6px">
				<span>Согласившись, таблицы модуля будут удалены, а потом воссозданы заново. Вы уверены?</span>
				</div>
				<form method="post" action="">
				<input type="submit" name="truncate" value="Очистить модуль" class="but">
				<input type="reset" onclick="javascript:history.back()" value="Отменить" class="but">
				</form>
				</fieldset>';
				if ($_POST['truncate'])
				{
					$sql = "TRUNCATE TABLE $mod_table";
					$modx->db->query($sql);
					$sql = "TRUNCATE TABLE $mod_table_th";
					$modx->db->query($sql);
					$sql = "TRUNCATE TABLE $start_table";
					$modx->db->query($sql);
					$sql = "TRUNCATE TABLE $tbl_results";
					$modx->db->query($sql);
					$sql = "TRUNCATE TABLE $tbl_results_balls";
					$modx->db->query($sql);
					header("Location: index.php?a=112&id=".$_REQUEST['id']);
				}
			}
			$tbl .= '<table class="zebra" cellpadding="1" cellspacing="1" style="font-size:12px">';
			$tbl .= '
			<thead>
				<tr align="center" valign="top">
					<td width="1%" align="center">
						<img src="/assets/modules/testing/images/test.png" height="16">
					</td>
					<td width="10%">
						Заголовок
					</td>
					<td width="50%">
						Описание
					</td>
										
					<td width="5%">
						Вопросы
					</td>
					
					<td width="5%">
						Результаты тестирования
					</td>
					
					<td width="7%" align="center">
						ID (пример вызова)
					</td>
					
					<td width="5%">
						Действия
					</td>
				</tr>
			</thead>
			';
			$i = 1;
			foreach ($res as $val)
			{
				$tbl .= '
				<tr>
					<td align="center" align="left" valign="top"';
					if ($val['test_type']==0)
					{
						$tbl .= ' class="test_type "';
					}
					elseif ($val['test_type']==1)
					{
						$tbl .= ' class="balls_type "';
					}
					$tbl .= '>
						<b>'.$i.'</b>
					</td>
					<td align="left" valign="top">
						'.$val['title'].'
					</td>
					<td align="left" valign="top">
						'.$val['description'].'
					</td>
					
					<td align="center" valign="top">
						<a href="'.$m_url.'&action=edit_questions&theme_id='.(int)$val['id'].'"><img src="/manager/media/style/MODxCarbon/images/icons/add.png"></a>
					</td>
					<td align="center" valign="top">
						<a href="'.$m_url.'&action=edit_theme&theme_id='.(int)$val['id'].'&act=results"><img src="/manager/media/style/MODxCarbon/images/icons/add.png"></a>
					</td>
					<td align="center" align="left" valign="top">
						<span class="ex">[!Testing? &id=`'.$val['id'].'`!]</span>	
					</td>
					<td align="center" valign="top">
						<a  title="Edit" href="'.$m_url.'&action=edit_theme&theme_id='.(int)$val['id'].'"><img src="'.$path.'images/edit.png"></a> 
						| 
						<a title="Delete" href="'.$m_url.'&action=del_theme&theme_id='.(int)$val['id'].'"><img src="/manager/media/style/MODxCarbon/images/icons/delete.gif"></a>
					</td>
				</tr>';
				$i++;
			}
			$tbl .= '
			<div id="actions">
			<ul class="actionButtons">
					<li id="Button1">
						<a href="'.$m_url.'&action=add_theme">
							 <img src="media/style/MODxCarbon/images/icons/save.png"> Добавить тест 
						</a>
					</li>
					<li id="Button3">
						<a href="'.$m_url.'&action=truncate">
							 <img src="media/style/MODxCarbon/images/icons/cancel.png"> Очистить таблицы 
						</a>
					</li>
					<li id="Button2">
						<a href="'.$m_url.'&action=drop">
							 <img src="media/style/MODxCarbon/images/icons/error.png"> Удалить модуль 
						</a>
					</li>
			</ul></div>
			';
			$tbl.='</table>';
		}
		if ($_GET['action']=='del_theme')
		{
			$out .='
			<fieldset><legend>Удаление темы и вопросов</legend>
			<form method="post" action="" class="margin">
				<div class="info">Вы действительно хотите удалить тему? Внимание! Вместе с темой удалятся и все созданные ранее вопросы!</div>
				<input type="submit" name="delete_this_theme" value="Удалить" class="but">
				<input type="reset" onclick="javascript:history.back()" value="Назад" class="but">
			</form>
			</fieldset>';
			$sql_th = "DELETE FROM $mod_table_th WHERE id ='".(int)$_GET['theme_id']."'";
			$sql_q = "DELETE FROM $mod_table WHERE id_theme ='".(int)$_GET['theme_id']."'";
			if ($_POST['delete_this_theme'])
			{
				$modx->db->query($sql_th);
				$modx->db->query($sql_q);
				header("Location: index.php?a=112&id=".$_REQUEST['id']."");
			}
		}
		$out.= $tbl;
		$out .= '</div>
		</body></html>';
	}
	
}
else
{
	echo 'Nain!';
}
if ($_GET['action']=='help')
	{		
		$out = '
		<div class="help-desc">
			<h2>Помощь по модулю </h2>
			<table class="tbl_help">
					<tr>
						<th>Установка модуля</th>
						<td>Зайдите в раздел "Модули" -> "Управление модулями". Создайте новый модуль: <br>Название - Тестирование. <br>Описание - модуль тестирования для MODX Evolution
						<br>Скопируйте в поле "Код модуля": 
						<b>include_once(MODX_BASE_PATH.\'assets/modules/testing/test.module.php\');</b>
						</td>
					</tr>
					<tr>
						<th>Конфигурация модуля</th>
						<td>Пусто...</td>
					</tr>
			</table>
			<h2>Помощь по сниппету </h2>
			<table class="tbl_help">
					<tr>
						<th>Установка сниппета</th>
						<td>Зайдите в раздел "Элементы" -> "Управление элементами" -> "Сниппеты" -> "Новый Сниппет". Создайте новый сниппет: <br>Название - Testing. <br>Описание - сниппет тестирования для MODX Evolution
						<br>Скопируйте в поле "Код сниппета": 
						<b><br>
						&lt;?
						<br>
						include_once(MODX_BASE_PATH.\'assets/modules/testing/test.snippet.php\');
						<br>
						?&gt;</b>
						</td>
					</tr>
					<tr>
						<th>Параметры сниппета</th>
						<td>
							<b>&id</b> - обязательный (указывает, из какой темы брать вопросы)<br>
							<b>&lang</b> - необязательный (язык сниппета принимает 2 значения ru и en)<br>
							<b>&container</b> - общий чанк-контейнер, в котором все и происходит. По умолчанию
							<code>
							&lt;div class="time_ost">[+time+]&lt;/div>	[+results+]	[+listing+]&lt;div class="quests">[+wrapper+]&lt;/div>
							</code><br>
							<b>&listingTpl</b> - чанк вывода ссылок на вопросы (навигация по вопросам). По умолчанию
							<code>
							&lt;div class="questions">&lt;ul>[+links+]&lt;/ul>&lt;/div>
							</code>
							<b>&answContainer</b> - чанк отображения контейнера ответов. По умолчанию
							<code>
							[+answers+]
							</code>
							<b>&answTpl</b> - Шаблон ответа. По умолчанию
							<code>
							&lt;li>&lt;b>[+a_num+])&lt;/b>[+answer+]&lt;/li>
							</code>
							<b>&aboutTpl</b> - Чанк отображения информации о теме тестирования. По умолчанию
							<code>
							&lt;div class="intro">&lt;h1>[+title+]&lt;/h1>[+description+]&lt;/div>
							</code>
							<b>&good </b> - Количество правильных ответов для того, чтобы сдать тест. По умолчанию 5
							<b>&min </b> - Время, отведенное на тестирование в минутах. По умолчанию 30
						</td>
					</tr>
					<tr>
						<th>Плейсхолдеры сниппета</th>
						<td>
						<b>[+title+]</b> - заголовок выбранной темы тестирования.<br>
						<b>[+description+]</b> - описание выбранной темы тестирования.<br>
						<b>[+time+]</b> - оставшееся время до окончания тестирования.<br>
						<b>[+results+]</b> - результаты после завершения тестирования.<br>
						<b>[+listing+] и [+links+]</b> - навигация по вопросам.<br>
						<b>[+wrapper+]</b> - выводит контейнер с вопросом.<br>
						<b>[+a_num+]</b> - выводит номер ответа.<br>
						<b>[+answer+]</b> - выводит ответ.
						</td>
					</tr>
			</table>
		</div>';
		$out .= '<div class="intro">
			<img src="'.$path.'images/logotype.png" style="display:block; margin:0 auto">
			<div class="text-intro"><b>MODX™ Testing</b> - это бесплатный модуль тестирования для CMS | CMF MODx Evolution.
				<small>Вопросы, помощь в доработке принимаются по адресу <a target="_blank" href="mailto:siuzi_drum@mail.ru">siuzi_drum@mail.ru</a></small>

			</div>
		</div>';
	}
echo $head.$out;
?>
