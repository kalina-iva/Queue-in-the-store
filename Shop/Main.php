<?php
namespace Shop;
include 'Constants.php';
include 'Customer_flow.php';
include 'Customer.php';
include 'Log.php';

$customer_flow = Customer_flow::get_customer_flow();					//генерируется поток покупателей

for($current_time = 1; $current_time <= 60 * DURATION_OF_WORKING_DAY; $current_time++)	//каждая итерация == минута рабочего дня
{
	$total_customers_per_minute = array_shift($customer_flow);			//сколько покупателей придет в текущую минуту
    
	for($i = 0; $i < $total_customers_per_minute; $i++)				//создается и инициализируется покупатель
	{
		$customer = new Customer(rand(1, 5));											
		++$total_customers_per_day;														
		$customer->initialize($kassir, $customer);
	}
	
	for($id = 0; $id < count((array)$kassir); $id++)				//перебираются все кассиры	
	{
		if($kassir[$id]->queue)							//если очередь не пустая
		{
			if((!$kassir[$id]->serves && !$kassir[$id]->expects) || 
			   ($kassir[$id]->serves == $current_time) || 
			   $kassir[$id]->expects)
			{
				$first_in_queue = array_shift($kassir[$id]->queue);	//кассир берет первого покупателя из очереди
				$kassir[$id]->expects = 0;				//касса не в режиме ожидания
				//высчитывается время обслуживания + текущее
				$kassir[$id]->serves = $first_in_queue->number_of_goods * PICK + TIME_TO_GET_PAY + $current_time;
			}
		}
		else													
		{
			if($kassir[$id]->serves == $current_time)
			{
				$kassir[$id]->expects = TIMEOUT + $current_time;	//высчитывается время ожидания покупателей	
				$kassir[$id]->serves = 0;
			}
			if($kassir[$id]->expects == $current_time)					
			{
				array_splice($kassir, $id, 1);						
			}
		}
	 }
	
	if(!($current_time % 60))							//каждый час выводится текущий результат
	{
        	Log::output($current_time, $kassir);
	}
}
echo "Обслужено за день: ", $total_customers_per_day, "\nФайл создан и лежит в папке с файлом кода";
?>
