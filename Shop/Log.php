<?php
namespace Shop;

class Log
{		
	//вывод инф. о состояниях магазина в файл
	function output($current_time, $kassir)
	{
		$filename = 'report.txt';												
		$fd = fopen($filename, 'a') or die("Не удалось создать/открыть файл");

		fwrite($fd, $current_time."\n");											//для лога по минутам (тек.минута)
		//fwrite($fd, ($current_time/60)." час\n");									//для лога по часам (тек.час)
		if($kassir)													
		{
			for($id=1; $id<=count($kassir); $id++)									//перебираются все кассы
			{
				//для лога по часам:
				//fwrite($fd, "Касса ".$id.". В очереди: ".count($kassir[$id-1]->queue)." чел.\n");	

				//для лога по минутам:
				fwrite($fd, $id.". ".$kassir[$id-1]->serves." ".$kassir[$id-1]->expects." ".count($kassir[$id-1]->queue)."\n");
			}
		}
		else
		{
			fwrite($fd, "Все кассы закрыты");
		}
		fwrite($fd, "\n");
		fclose($fd);								
	}
}
?>