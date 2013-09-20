<?
/*
CREATE TABLE `modx3`.`modx3_test_started` (
`id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
`session_id` VARCHAR( 255 ) NOT NULL ,
`time_death` VARCHAR( 255 ) NOT NULL ,
`questions_id` TEXT NOT NULL ,
`answers` TEXT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;
*/
session_start();

// question chunk
$qTpl = isset($qTpl) ? $modx->getChunk($qTpl) : '
	<li>
		<h3>Вопрос [+num+]</h3>
		<b>[+question+]</b><br>
		[+answers+]
	</li>';

//global $modx;
$sess_id = 0 ? $sess_id : session_id();
$testTimeDeath = time() + 30 * 60 + 1;
$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$start_table = $dbprefix."test_started"; //таблица модуля
$q_table = $dbprefix."test_questions"; //таблица модуля
//echo $start_table;

$theme = $modx->config['manager_theme']; //тема админки
$basePath = $modx->config['base_path']; //путь до сайта на сервере

$path = $modx->config['site_url'].'assets/modules/testing/';

$if_test =  $modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'");
// проверка 

$res = $modx->db->getRow($if_test);

$min = isset($min) ? $min :30;

$id = isset($id) ? $id : 1;
$th_id = $id;
$out ='';
$out .= ' <link rel="stylesheet" href="'.$path.'css/testing.css" type="text/css" media="screen" />';
if ($_GET['act'] =='go' and $_GET['quest'])
{
	$time = date("Y-m-d H:i",mktime(date("H"),date("i")+$min,0,date("m"),date("d"),date("Y")));
	//echo date("Y-m-d H:i").' а в БД идет-'.$time.'<br>';
	if ($modx->db->getRecordCount($modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'")) == 0)
	{
	$modx->db->query("INSERT INTO $start_table VALUES ('NULL','$sess_id','".$time."','','')");
	header ('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest=1');
	}
	if ($if_test !==0)
	{ 
		//$out.= 'Идентификатор сессии - '.$sess_id.'<br>';
		$out .= 'Время на тестирование - ';
		$out .= '<SCRIPT language="JavaScript" SRC="/assets/modules/testing/count/countdown.php?countto='.$res['time_death'].'&do=t&data=Закончен тест&do=r&data=index.html?act=end"></SCRIPT>';
		$out .= '<h2>Название тестирования</h2>';
		$sql = "SELECT * FROM $q_table WHERE id_theme = '$th_id'";
		$res = $modx->db->query($sql);
		$res = $modx->db->makeArray($res);
		$i = 0;$j=1;
		$_GET['quest'] = (int)$_GET['quest'];
		$arr_q = array();
		$out .= '<div class="questions">';
		$out .='<ul>';
		foreach ($res as $r)
		{
			if ($_GET['quest'] > $modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'")))
			{
				header ('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=end');
			}
			if ($_GET['quest'] == $j)
			{
				$out.= '<li  class="active"><a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($j).'">Вопрос '.($j).'</a></li>';
			}
			else
			{
				$out.= '<li><a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($j).'">Вопрос '.($j).'</a></li>';
			}
			$j++;
			//else
			//{
			//	$out .= 'Вы что-то поменяли тут... нет такого вопроса.';
			//}
		}
		$out .='</ul>';
		$out .= '</div>';
		$out .= '<div class="answers">';
		foreach ($res as $key =>$val)
		{
				//echo '<pre>';
				//print_r ($val);
				//echo '</pre>';
				//$out.= '<a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($i+1).'">Вопрос '.($i + 1).'</a><br>';
				$arr_q[($i+1)] = $val['id'];
				if ($_GET['quest'] == ($i+1))
				{
					//получаем вопрос
					$sql = "SELECT * FROM $q_table WHERE id_theme = '$th_id' AND id = '".$arr_q[($i+1)]."'";
					$res = $modx->db->getRow($modx->db->query($sql));
					$out .= '<h3>Вопрос '.($i+1).'</h3>';
					$out .= '<b>'.$res['question'].'</b><br>';
					$answ = $res['json_answ'];
					$answ = explode(';',$answ);
					$ii=1;
					$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
					$result = $modx->db->makeArray($modx->db->query($sql));
					//print_r($res['questions_id']);
					$res = explode (',',$result[0]['questions_id']);
					$res2 = explode (',',$result[0]['answers']);
					foreach ($res as $key => $value) 
						{
							if ($value == '')unset($res[$key]);
						}
					foreach ($res2 as $key => $value) 
						{
							if ($value == '')unset($res2[$key]);
						}
					/*
					Echo 'Массив с ИД вопросов из БД';
					echo '<pre>';
					print_r($res);
					echo '</pre>';
					Echo 'Массив с ответами из БД';
					echo '<pre>';
					print_r($res2);
					echo '</pre>';
					*/
					$out.='<form method="post" action="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($_GET['quest']+1).'">';
					$out .='<ul class="quest">';
					if (in_array($_GET['quest'],$res))
					{
						$out .= '<i>Вы уже ответили на этот вопрос</i><br>';
						foreach ($answ as $a)
						{
								$a = json_decode($a, true);
								$out.= $ii.') '.$a['answer'].'<br>';
							
							$ii++;
						}
						$out.='</ul>';
					}
					else
					{
						foreach ($answ as $a)
						{
								$a = json_decode($a, true);
								$answers[$ii] = $_POST['answ'].',';
								$out.='<li>';
								$out.= $ii.') <input type="radio" name="answ" value="'.$a['ball'].'">'.$a['answer'].'</li>';
								$ii++;
						}
						// проверка на кол во страниц
						$out.='</ul><input type="submit" name="s_answ" value="Ответить">';
					}
					$out.='<input type="submit" name="game_over" value="Сдать тест"></form>';
					
					$out .= $qTpl;
					
				}				
			$i++;
		}
		$out .= '</div>';
		if ($_GET['quest'] <= ($modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'"))+1))
							{								
								
								$answers = $_POST['answ'].',';
								//echo $modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'"));
								if ($_POST['s_answ'])
								{
									$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
									$itog = $modx->db->query($sql);
									$itog = $modx->db->getRow($itog);
									//print_r($itog);
									$it = explode(',',$itog['answers']);
									foreach ($it as $key => $value) {
										if ($value == '')unset($it[$key]);
									}
									$q_id = $itog['questions_id'].($_GET['quest']-1).',';
									//echo $q_id;
									//print_r($it);
									if ($it)
									{
										$answers = $itog['answers'].$answers;
									}
									$out .= $answers;
									$sql = "UPDATE $start_table SET answers = '".$answers."',questions_id ='".($q_id)."' WHERE session_id = '$sess_id'";
									$modx->db->query($sql);
									header('Location:'.$_SERVER['REQUEST_URI']);
								}
							}
							else
							{
								unset($sess_id);
							}
							

	}	

}
elseif ($_GET['act']=='go' and !$_GET['quest'])
{
	header('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest=1');
}
elseif($modx->db->getRecordCount($modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'")) !== 0 and !$_GET['act'])
{
		header('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=go');
}
elseif ($modx->db->getRecordCount($modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'")) == 0)
{
	$out .= '<a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest=1">Начать тестирование</a>';
}
if ($_POST['game_over'])
{
	header('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=end');
	//session_destroy();
}
if ($_GET['act'] =='end')
{
	//unset($sess_id);
	//$out .= $sess_id;
	$good = isset($good) ? $good : 3;

	$out .= '<h1>Подведение итогов тестирования</h1>';
	$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
	if($modx->db->getRecordCount($modx->db->query($sql)) > 0)
	{
		$result = $modx->db->makeArray($modx->db->query($sql));
	
		//print_r($res['questions_id']);
		$res2 = explode (',',$result[0]['answers']);
		foreach ($res as $key => $value) 
		{
			if ($value == '')unset($res[$key]);
		}
		$balls= array_sum($res2);
		$t = array('балл', 'балла', 'баллов');
		function declOfNum($number, $titles)
		{
			$cases = array (2, 0, 1, 1, 1, 2);
			return $number." ".$titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
		}
		$out .= 'Вы набрали '. declOfNum($balls, $t).' из '.$modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'"));
		if ($balls >= $good)
		{
			$out .= '<br>Поздравляем вас! Вы прошли тестирование!';
		}
		else
		{
			$out .='<br><i>К сожалению, Вы не прошли тест</i>';
		}
		mysql_query("DELETE FROM $start_table WHERE session_id = '$sess_id'");
		session_destroy();
	}
	else
	{
		header ('Location:'.$modx->makeUrl($modx->documentIdentifier));
	}

}
echo $out;
//выводим заголовок теста
?>