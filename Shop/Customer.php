<?php
namespace Shop;
include 'Constants.php';
include 'Kassir.php';

class Customer
{
	public $number_of_goods;					//сколько покупатель набрал продуктов
    
	function __construct($number)
	{
		$this->number_of_goods = $number;
	}
	
	function initialize(&$kassir, $customer)
	{
		if($kassir)	
		{
			usort($kassir, function($kassir1,$kassir2)	//массив касс сортируется по возрастанию кол-ва чел. в очереди
			  {
				  if(count($kassir1->queue) == count($kassir2->queue)) return 0;
				  return (count($kassir1->queue) > count($kassir2->queue)) ? 1 : -1;
			  });
		}

		//если есть место в очереди или работают все кассы
		if(count((array)$kassir[0]->queue) < TOTAL_IN_QUEUE || 
		   count($kassir) == TOTAL_KASS)
		{
			$kassir[0]->queue[] = $customer;					
		}

		//если не все кассы работают
		if((count($kassir[0]->queue) >= TOTAL_IN_QUEUE && count($kassir) < TOTAL_KASS) || 
		   !$kassir)
		{
			$kassa = new Kassir($customer);
    			$kassir[] = $kassa;						
		}
	}
}

?>
