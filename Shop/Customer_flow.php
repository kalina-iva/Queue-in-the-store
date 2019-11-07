<?php
namespace Shop;

include 'Constants.php';

class Customer_flow
{
	const MAX_N_CUSTOMERS_PER_MINUTE = 1;

	function get_customer_flow()
	{
		$part1 = Customer_flow::generate_exponential_array();			//генерируется массив для первой половины дня
		$temp = Customer_flow::generate_exponential_array();			//генерируется массив для второй половины дня
		$part2 = array_reverse($temp);									//реверсируется массив для второй половины дня, чтобы значения убывали
		return array_merge_recursive($part1, $part2);					//соединяются массивы и возвращаются
	}
	
	//генерируется случайная величина по закону экспоненциального распределения
	//затем проверяется есть ли в массиве элемент с таким порядковым номером
	function generate_exponential_array()
	{
		for ($minute = 0; $minute < 60 * DURATION_OF_WORKING_DAY/2; $minute++)
		{
			$exponential_value = (int) (100 * log(rand(3, 500)));						
			$temp[$exponential_value] = isset($temp[$exponential_value]) ? 1 : 0;
			$exponential_array[] = $temp[$exponential_value] * rand(1,self::MAX_N_CUSTOMERS_PER_MINUTE);
		}
		return $exponential_array;
	}
}

?>
