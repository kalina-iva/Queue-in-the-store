<?php
namespace Shop;

class Kassir
{
    public $serves = 0;			//до какой минуты обслуживается покупатель
    public $expects = 0;		//до какой минуты простаивает касса
    public $queue = [];			//очередь в кассу
	
	function __construct($customer)
    {
        $this->queue[] = $customer;
    }
}

?>