<?php
namespace MP\event;

/*                                                                                 

___  ___                      ______ _           
|  \/  |                      | ___ \ |          
| .  . | ___  _ __   ___ _   _| |_/ / |_   _ ___ 
| |\/| |/ _ \| '_ \ / _ \ | | |  __/| | | | / __|
| |  | | (_) | | | |  __/ |_| | |   | | |_| \__ \
\_|  |_/\___/|_| |_|\___|\__, \_|   |_|\__,_|___/
                          __/ |                  
                         |___/                   
by gigantessbeta[みやりん]
*/

use MP\DataAccess\YamlManager;
use MP\MoneyPlusAPI;
use pocketmine\event\Cancellable;
use pocketmine\event\plugin\PluginEvent;

class UserRegisterEvent extends PluginEvent implements Cancellable{

	public static $handlerList;
	private $ym;

	public function __construct($name, $defmoney, $case){
		$this->name = $name;
		$this->df = $defmoney;
		$this->case = $case;
	}


	public function getPlayerName(){
		return $this->name;
	}


	public function getMoney(){
		return $this->def;
	}


	public function getCase(){
		return $this->case;
	}
}