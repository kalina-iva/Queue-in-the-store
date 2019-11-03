<?php

define("N_KASS", 3);    	//кол-во касс в магазине
define("N_QUEUE", 5);   	//кол-во человек в очереди
define("PICK", 1);		//время на пробивку товара (в мин)
define("GET_PAY", 2);   	//время на оплату товара (в мин)
define("SMENA", 8);     	//длительность рабочего дня
define("DELAY", 5);		//сколько кассир ожидает покупателей (в мин)
define("M", 100);		//интенсивность потока покупателей

$n_customers = 0;       	//кол-во посетителей в магазине

class Kassir
{
    public $status = 0;		//обслуживает ли касса посетителя
    public $until = 0;		//до какой минуты обслуживается покупатель
    public $delay = 0;		//до какой минуты простаивает касса
    public $queue = [];		//очередь в кассу
}

class Customer
{
    public $n_goods;		//сколько покупатель набрал продуктов
    
    public function __construct($n_goods)
    {
        $this->n_goods = $n_goods;
    }
}

//генерация потока
//в этом магазине все предопределено
function gen_exp()
{
    for ($m = 0; $m < 60 * SMENA/2; $m++)		//на каждую минуту р.д. определяется флаг прихода покупателя
    {
        $rnd = (int) (M * log(rand(3, 500)));		
        $temp[$rnd] = isset($temp[$rnd]) ? 1 : 0;
        $part[] = $temp[$rnd];
    }
    return $part;
}

function gen_potok()
{
    $part1 = gen_exp();					//генерируется массив для первой половины дня
    $temp = gen_exp();					//генерируется массив для второй половины дня
    $part2 = array_reverse($temp);			//реверсируется массив для второй половины дня, чтобы значения убывали
    return array_merge_recursive($part1, $part2);	//соединяются массивы и возвращаются
}

//создание покупателя
function create_customer(&$kassir)
{
    global $n_customers;							//счетчик покупателей за день
    ++$n_customers;
    $customer = new Customer(rand(1, 5));					//инициализируется объект класса Customer
    if($kassir)									//если есть рабочие кассы
    {
		usort($kassir, function($kassir1,$kassir2)			//массив сортируется по возрастанию кол-ва чел. в очереди
			  {
				  if($kassir1->queue == $kassir2->queue) return 0;
				  return ($kassir1->queue > $kassir2->queue) ? 1 : -1;
			  });
		
        if(count($kassir[0]->queue) < N_QUEUE || count($kassir) == N_KASS)	//если место в очереди есть или все кассы работают
        {
            $kassir[0]->queue[] = $customer;					//покупатель встает в очередь
        }
        else
        {
            if(count($kassir) < N_KASS)						//если не все кассы работают
            {
                create_kassir($kassir, $customer);				//"открывается" касса
            }
        }
    }
    else
    {
        create_kassir($kassir, $customer);					//если все кассы были закрыты
    }
}

//создание кассира
function create_kassir(&$kassir, $customer)
{
	$kassa = new Kassir();
    $kassa->queue[] = $customer;
    $kassir[] = $kassa;
}

//кассир обслуживает покупателя
function serve_customer(&$kassir, $j, $t_cur)
{
    $cus = array_shift($kassir[$j]->queue);					//кассир берет первого покупателя из очереди
    $kassir[$j]->status = 1;							//касса обслуживает покупателя
    $kassir[$j]->delay = 0;							//касса не в режиме ожидания
    $kassir[$j]->until = $cus->n_goods * PICK + GET_PAY + $t_cur;		//высчитывается время обслуживания + текущее
}

//вывод инф. о состояниях магазина в файл
function output_log($i, $kassir)
{
    $filename = 'report.txt';							//название файла
    $fd = fopen($filename, 'a') or die("Не удалось создать/открыть файл");	//файл открывается
    
    //fwrite($fd, $i."\n");							//для лога по минутам (тек.минута)
	fwrite($fd, ($i/60)." час\n");						//для лога по часам (тек.час)
    if($kassir)									//есть рабочие кассы
    {
        for($j=1; $j<=count($kassir); $j++)					//перебираются все кассы
        {
			//для лога по часам:
            fwrite($fd, "Касса ".$j.". В очереди: ".count($kassir[$j-1]->queue)." чел.\n");	
			
			//для лога по минутам:
			//fwrite($fd, $j.". ".$kassir[$j-1]->until." ".$kassir[$j-1]->delay." ".count($kassir[$j-1]->queue)."\n");
        }
    }
    else
    {
        fwrite($fd, "Все кассы закрыты");
    }
    fwrite($fd, "\n");
    fclose($fd);								//файл закрывается
}


//main
$potok = gen_potok();								//генерируется поток покупателей

for($t_current = 1; $t_current <= 60 * SMENA; $t_current++)			//каждая итерация == минута рабочего дня
{
    $come = array_shift($potok);						//извлекается первый эл.массива потока
    if($come)									//покупатель пришел
    {
        create_customer($kassir);						//покупатель создается
    }
    if($kassir)									//есть рабочие кассы
    {
        for($k=0; $k<count($kassir); $k++)					//кассы перебираются
        {
            if($kassir[$k]->status)						//кассир обслуживает покупателя
            {
                if($kassir[$k]->until == $t_current)				//покупатель обслуживался до тек. минуты
                {
                    if($kassir[$k]->queue)					//в очереди есть еще покупатели
                    {
                        serve_customer($kassir, $k, $t_current);		//берется очередной и обслуживается
                    }
                    else							//в очереди больше никого нет
                    {
                        $kassir[$k]->delay = DELAY + $t_current;		//засекается время сколько кассир ожидает
                        $kassir[$k]->status = 0;				//кассир не обслуживает
                        $kassir[$k]->until = 0;
                    }
                }
            }
            else								//касса не обслуживает покупателя
            {
                if($kassir[$k]->queue)						//появился покупатель в очереди
                {
                    serve_customer($kassir, $k, $t_current);			//покупатель обслуживается			
                }
                else								//покупатель не появился в очереди
                {
                    if($kassir[$k]->delay == $t_current)			//и время ожидания кассира вышло
                    {
                        array_splice($kassir, $k, 1);				//он уходит пить чай
                    }
                }
            }
        }
    }
    if(!($t_current % 60))							//каждый час выводим лог
    {
        output_log($t_current, $kassir);
	}
}
echo "Обслужено за день: ", $n_customers, "\nФайл создан и лежит в папке с файлом кода";
?>
