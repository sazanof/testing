<?php
/*
# MODX TESTING SNIPPET
# AUTHOR SAZANOF siuzi_drum@mail.ru
# VERSION 0.1.0
# 04.04.2013

ALTER TABLE `modx3_test_started` ADD `id_th` INT NOT NULL AFTER `answers` 

*/
session_start();
$lang = isset($lang) ? $lang : 'ru';
include (MODX_BASE_PATH.'assets/modules/testing/lang/lang.'.$lang.'.php');

	//
$container = isset($container) ? $modx->getChunk($container) : '<div class="time_ost">[+time+]</div>[+results+][+listing+]<div class="quests">[+wrapper+]</div>';
	// links to questions tpl
$listingTpl = isset($listingTpl) ? $modx->getChunk($listingTpl) : '<div class="questions"><ul>[+links+]</ul></div>';
	// question chunk
$qTpl = isset($qTpl) ? $modx->getChunk($qTpl) : '
	<div class="quest">
		<span class="q_text"><b>[+question+]</b> ('.$lang[0].'[+num+])</span>
		<form method="post" action="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($_GET['quest']+1).'">
		<ul class="answers">[+answers+]</ul>
		[+submit+][+over+]
		</form>
	</div>';
$answContainer = isset($answContainer) ? $modx->getChunk($answContainer) : '[+answers+]';

$answTpl = isset($answTpl) ? $modx->getChunk($answTpl) :'<li><b>[+a_num+])</b><img src="[(site_url)][+img_var+]" width="200">[+answer+]</li>';

$aboutTpl = isset($aboutTpl) ? $modx->getChunk($aboutTpl) : '<div class="intro"><h1>[+title+]</h1>[+description+]</div>';

$showThemeInfo = isset($showThemeInfo) ? $showThemeInfo : 1; 
if($showThemeInfo == 0) $aboutTpl='';
elseif($showThemeInfo > 1) $aboutTpl='Wrong parameter <b>&showThemeInfo</b>!!! Only 0 or 2';

$sortBy = isset($sortBy) ? $sortBy : 'id';

$orderBy = isset($orderBy) ? $orderBy : 'ASC';

$display = isset($display) ? $display : 'themes';

$sess_id = 0 ? $sess_id : session_id();
$testTimeDeath = time() + 30 * 60 + 1;
$dbname = $modx->db->config['dbase']; //имя базы данных
$dbprefix = $modx->db->config['table_prefix']; //префикс таблиц
$start_table = $dbprefix."test_started"; //таблица модуля
$q_table = $dbprefix."test_questions"; //таблица модуля
$th_table = $dbprefix."test_themes"; //таблица модуля
//echo $start_table;

$theme = $modx->config['manager_theme']; //тема админки
$basePath = $modx->config['base_path']; //путь до сайта на сервере



$path = $modx->config['site_url'].'assets/modules/testing/';

$if_test =  $modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'");
// проверка 

$res = $modx->db->getRow($if_test);

$min = isset($min) ? $min :30;

$id = isset($id) ? $id : 1;
// недоработано
if ($id == 'rand')
{
	$sq = "SELECT id FROM $th_table";
	$s = "SELECT * FROM $start_table WHERE session_id = '$sess_id' ";
	if ($modx->db->getRecordCount($modx->db->query($s)) == 0)
	{
		$r = $modx->db->query($sq);
		$r = $modx->db->makeArray($r);
		$id = (array_rand($r) + 1);
		echo $id;
	}
	else
	{
		$r = $modx->db->query($s);
		$r = $modx->db->getRow($r);
		$id = $r['id_th'];

	}
	
}

$th_id = $id;

$out ='';
$out .= ' <link rel="stylesheet" href="'.$path.'css/testing.css" type="text/css" media="screen" />';
if ($_GET['act'] =='go' and $_GET['quest'])
{
	$sql = "SELECT time FROM $th_table WHERE id = $id";
	$testI = $modx->db->query($sql);
	$testI = $modx->db->getRow($testI);
	// время на тест из бд
	if($testI['time']>0){
		$time = date("Y-m-d H:i",mktime(date("H"),date("i")+$testI['time'],0,date("m"),date("d"),date("Y")));
	
		if ($modx->db->getRecordCount($modx->db->query("SELECT * FROM $start_table WHERE session_id = '$sess_id'")) == 0)
		{
			$modx->db->query("INSERT INTO $start_table VALUES ('NULL','".$modx->db->escape($sess_id)."','".$modx->db->escape($time)."','','','".$modx->db->escape($id)."')");
			header ('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest=1');
		}
	}
	if ($if_test !==0)
	{ 
		$time = '<SCRIPT language="JavaScript" SRC="/assets/modules/testing/count/countdown.php?countto='.$res['time_death'].'&do=r&data='.$modx->makeUrl($modx->documentIdentifier).'?act=end"></SCRIPT>';
		$sql = "SELECT * FROM $q_table WHERE id_theme = '$th_id'  ORDER BY $sortBy $orderBy";
		$res = $modx->db->query($sql);
		$res = $modx->db->makeArray($res);
		$i = 0;$j=1;
		$_GET['quest'] = (int)$_GET['quest'];
		$arr_q = array();
		
		$links = array();
		foreach ($res as $r)
		{
			if ($_GET['quest'] > $modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'")))
			{
				header ('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=end');
			}
			if ($_GET['quest'] == $j)
			{
				$links[] = '<li class="active"><a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($j).'">'.$lang[1].' '.($j).'</a></li>';
			}
			else
			{
				$links[] = '<li><a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest='.($j).'">'.$lang[1].' '.($j).'</a></li>';
			}
			$j++;
		}
		$links = implode('',$links);
		$modx->setPlaceholder('links',$links);
		foreach ($res as $key =>$val)
		{
				$arr_q[($i+1)] = $val['id'];
				if ($_GET['quest'] == ($i+1))
				{
					//получаем вопрос
					$sql = "SELECT * FROM $q_table WHERE id_theme = '$th_id' AND id = '".$arr_q[($i+1)]."'";
					$res = $modx->db->getRow($modx->db->query($sql));
					$num = $i+1;
					$question = $res['question'];
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
					
					$arr = array();
					if (in_array($_GET['quest'],$res))
					{
						foreach ($answ as $a)
						{
								$a = json_decode($a, true);
								
								$a_num = $ii;
								$answer = $a['answer'];
								$f = array ('[+a_num+]','[+answer+]');
								$r = array ($a_num,$answer);
								$ans = str_replace ($f,$r,$answTpl);
								$arr[] = $ans;
							
							$ii++;
						}
						$arr = implode('',$arr);
					}
					else
					{
						foreach ($answ as $a)
						{
								$a = json_decode($a, true);
								$a_img = $a['imgs'];
								//echo $a_img;
								//print_r($a);
								$answers[$ii] = $_POST['answ'].',';
								$a_num = $ii;
								$answer = '<input type="radio" name="answ" value="'.$a['ball'].'">'.$a['answer'];
								$f = array ('[+a_num+]','[+answer+]','[+img_var+]');
								$r = array ($a_num,$answer,$a_img);
								$ans = str_replace ($f,$r,$answTpl);
								$arr[] = $ans;
								$ii++;
						}
						$arr = implode('',$arr);
						// проверка на кол во страниц
						$submit = '<input type="submit" name="s_answ" value="'.$lang[2].'" class="button" style="margin-right:4px">';
					
					}
					$over = '<input type="submit" name="game_over" value="'.$lang[3].'" class="button">';

					$fields = array('[+num+]','[+question+]','[+answers+]');
					$replace = array($num,$question,$arr);
					$qTpl = str_replace($fields,$replace,$qTpl);
				}				
			$i++;
		}
		if ($_GET['quest'] <= ($modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'"))+1))
							{								
								if ($_POST['answ']=='') $_POST['answ'] = "no";
								$answers = $_POST['answ'].',';
								if ($_POST['s_answ'])
								{
									$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
									$itog = $modx->db->query($sql);
									$itog = $modx->db->getRow($itog);
									$it = explode(',',$itog['answers']);
									foreach ($it as $key => $value) {
										if ($value == '')unset($it[$key]);
									}
									$q_id = $itog['questions_id'].($_GET['quest']-1).',';
									if ($it)
									{
										$answers = $itog['answers'].$answers;										
									}
									$sql = "UPDATE $start_table SET answers = '".$modx->db->escape($answers)."',questions_id ='".$modx->db->escape($q_id)."' WHERE session_id = '$sess_id'";
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
	$sql = "SELECT * FROM $th_table WHERE id ='$id'";
	$res = $modx->db->getRow($modx->db->query($sql));
	$title = $res['title'];
	$description = $res['description'];
	$container = '[+about_test+]<center><a href="'.$modx->makeUrl($modx->documentIdentifier).'?act=go&quest=1" class="button">'.$lang[10].'</a></center>';
}
if ($_POST['game_over'])
{
	header('Location:'.$modx->makeUrl($modx->documentIdentifier).'?act=end');
	//unset($sess_id);
}
if ($_GET['act'] =='end')
{
	$qTpl='';
	$good = isset($good) ? $good : 5;

	$results = '<h1>'.$lang[4].'</h1>';
	$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
	if($modx->db->getRecordCount($modx->db->query($sql)) > 0)
	{
		$result = $modx->db->makeArray($modx->db->query($sql));
		if($result[0]['answers']=='') 
		{
			$results .= $lang[11];
		}
		else
		{
			//print_r($res['questions_id']);
			$res2 = explode (',',$result[0]['answers']);
			foreach ($res as $key => $value) 
			{
				if ($value == '')unset($res[$key]);
			}
			$balls= array_sum($res2);
			$t = array($lang[5][0], $lang[5][1], $lang[5][2]);
			function declOfNum($number, $titles)
			{
				$cases = array (2, 0, 1, 1, 1, 2);
				return $number." ".$titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
			}
			// write results
			$sql = "SELECT * FROM $th_table WHERE id='$id'";
			$it = $modx->db->getRow($modx->db->query($sql));
			if ($it['test_type']==0)
			{
				// если тип выводимых результатов - testirovanie
				$results .= $lang[6].' [+earned+]';
				$select = "SELECT * FROM ".$modx->getFullTableName('test_results')." WHERE min < $balls AND max >= $balls AND id_test = '$id'";
				$res = $modx->db->query($select);
				$res = $modx->db->getRow($res);
				//print_r($res);
				$results .= '<br><b>Ваш результат:</b>';
				$results .='<div class="your_res">
				<div class="res_balls">'.$res['min'].' - '.$res['max'].' баллов</div><br>';
				$results .= $res['res'];
				$results .= '</div>';
			}
			elseif ($it['test_type']==1)
			{
				// если тип выводимых результатов - баллы
				$sql = "SELECT * FROM ".$modx->getFullTableName('test_results_balls')." WHERE id='".$id."'";
				$row = $modx->db->getRow($modx->db->query($sql));
				$results .= '<div style="padding:10px; margin:10px 0; background:#ddd"><h1>'.$row['title'].'</h1>';
				$results .= str_replace('[+balls+]',$balls,$row['descr']).'</div>';
				if ($balls >= $good)
				{
					$results .= $lang[8];
				}
				else
				{
					$results .= $lang[9];
				}
				
				
				$sql = "SELECT * FROM $start_table WHERE session_id = '$sess_id'";
				$res = $modx->db->getRow($modx->db->query($sql));
				$a_q = $res['questions_id'];
				$a_q = explode (',',$res['questions_id']);
				unset ($a_q[count($a_q)-1]);
				
				$a_ans = $res['answers'];
				$a_ans = explode (',',$res['answers']);
				unset ($a_ans[count($a_ans)-1]);
				$results .= "<hr>";
				
				$a_ans = array_combine($a_q,$a_ans);
				
				$sql_res = "SELECT * FROM $q_table WHERE id_theme='$id'";
				$res_res = $modx->db->makeArray($modx->db->query($sql_res));
				
				$answers_j = json_decode($res_res['json_answ']);
				
				foreach ($a_ans as $key => $value)
				{
					if ($value == "no")
					{
						$str_arr = $res_res[($key-1)];
						$qarr = $res_res[($key-1)]['json_answ'];
						$qarr = explode (';',$qarr);
						$str = json_decode($qarr[($res_res[($key-1)]['correct_answ']-1)],true);
						$results .= "Вопрос:<br><b style=\"color:red\">".$res_res[($key-1)]['question']."</b><br>";
						$results .= "<b style=\"color:red\">Нет ответа</b><br>Правильный ответ: ".$str['answer'].'<br><hr>';
					}
					elseif ($value == 0)
					{
						$str_arr = $res_res[($key-1)];
						$qarr = $res_res[($key-1)]['json_answ'];
						$qarr = explode (';',$qarr);
						$str = json_decode($qarr[($res_res[($key-1)]['correct_answ']-1)],true);	
							
						$results .= "Вопрос:<br><b style=\"color:orange\">".$res_res[($key-1)]['question'].'</b><br>'; 
						$results .= "Ответ Неверный<br>";
						$results .= "Правильный ответ: ".$str['answer'].'<br><hr>';
						//$results .= $qarr[($res_res[$key]['correct_answ']-1)]."<hr>";				
					}
					elseif ($value > 0)
					{
						$str_arr = $res_res[($key-1)];
						$qarr = json_decode($res_res[($key-1)]['json_answ']);
						$results .= "Вопрос:<br><b style=\"color:green\">".$res_res[($key-1)]['question'].'</b><br>';
						$results .= "Ответ правильный!<br><hr>";
					}
					
				}
				
			}
			$modx->setPlaceholder('total',$modx->db->getRecordCount($modx->db->query("SELECT * FROM $q_table WHERE id_theme = '$th_id'")));
			$modx->setPlaceholder('earned',declOfNum($balls, $t));
		}
		
			$modx->db->query("DELETE FROM $start_table WHERE session_id = '$sess_id'");
			unset($sess_id);
		
	}
	else
	{
		header ('Location:'.$modx->makeUrl($modx->documentIdentifier));
	}

}
$modx->setPlaceholder('title',$title);
$modx->setPlaceholder('description',$description);
$modx->setPlaceholder('about_test',$aboutTpl);

$modx->setPlaceholder('time',$time);

$modx->setPlaceholder('listing',$listingTpl);
$modx->setPlaceholder('wrapper',$qTpl);
$modx->setPlaceholder('results',$results);

$modx->setPlaceholder('submit',$submit);
$modx->setPlaceholder('over',$over);

if ($_GET['act'] == 'end')$qTpl='';

echo $out.$container;
//echo $out;
//выводим заголовок теста
?>