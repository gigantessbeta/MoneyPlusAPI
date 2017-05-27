<?php

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

namespace GE;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use GE\FunctionConnectManager;
use GE\YamlManager;

class MoneyPlusAPI extends PluginBase implements Listener{

	const Prefix = "§7[§bMP§7]§f ";
	const Cver = 1;

	public function onEnable(){
		$this->getLogger()->info("\n\n [§6========== §b MoneyPlus §6 ==========§f]\n§aMoneyPlusをご利用くださり、まことにありがとうございます。\n§c二次配布・改造配布・再配布・その他譲渡行為は厳禁です。\n§e作者: gigantessbeta §btwitter @gigantessbeta\n");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->y = new YamlManager($this);
		if($this->y->getData("config-version") != MoneyPlusAPI::Cver){
			$this->getLogger()->notice(MoneyPlusAPI::Prefix."§c You need to renew the version of Config. Delete the existing Config file, restart it and update it.");
		}
		$this->unit = $this->y->getData("unit");
	}

	public function Join(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if(!$this->y->exist($name)){
			$player->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("register")."");
			$this->y->setPlayerData($name);
		}

	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch(strtolower($command->getName())){
			case "m":
				if(!isset($args[0])){
					$a = $this->y->getData("help");
						$sender->sendMessage("§l§e[ §bMoneyPlus's help §e]");
					foreach($a as $aho => $b){
						$sender->sendMessage("".$b."");
					}
					return true;
					
				}
				if($sender instanceof Player){
					$smoney = $this->y->getMoney($sender->getName());
					$check = true;
				}else{
					$check = false;
				}
		

				switch($args[0]){

					case "check":
						if($check === false){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-console")."");
							return true;
						}
						$message = $this->y->getData("command-check");
						$sender->sendMessage(str_replace(array('%a', '%b'), array($smoney, $this->unit), MoneyPlusAPI::Prefix.$message));
						return true;
						break;

					case "view":

						if(!isset($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}elseif(!$this->y->exist($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-not")."");
							return true;
						}

						$message = $this->y->getData("command-view");
						$money = $this->y->getMoney($args[1]);
						$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $money, $this->unit),  MoneyPlusAPI::Prefix.$message));
						return true;
						break;

					case "pay":
						if($check === false){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-console")."");
							return true;
						}
						if(!isset($args[2]) || !is_numeric($args[2])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}elseif(!$this->y->exist($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-not")."");
							return true;
						}


						$message = $this->y->getData("command-pay");
						$result = $smoney - $args[2];
						if($result < 0){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-money")."");
							return true;
						}

						$this->y->takeMoney($sender->getName(), $args[2]);
				 		$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  MoneyPlusAPI::Prefix.$message));
						$this->y->addMoney($args[1], $args[2]);

						if($this->getServer()->getPlayer($args[0]) != null){
							$message2 = $this->y->getData("money-received");
							$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($sender->getName(), $args[2], $this->unit),  MoneyPlusAPI::Prefix.$message2));
						}
						return true;
						break;

					case "rank":
						if(!isset($args[1])){
							$args[1] = 1;
						}elseif(!is_numeric($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}
						$all = $this->y->getAllMoney();
						$max = 0;
						foreach($all as $c){
							$max += count($c);
						}
						$max = ceil(($max / 5));
						$page = max(1, $args[1]);
						$page = min($max, $page);
						$page = (int) $page;
						$sender->sendMessage("===[".$this->y->getData("command-rank")."".$page."/".$max."]===");
						arsort($all);
						$oprank = $this->y->getData("ranking-op-enable");
						$i = 0;
						foreach($all as $a => $b){
							$a = strtolower($a);
							if(isset($this->getServer()->getOps()->getAll()[$a]) && $oprank == "false"){
								continue;
							}
								if(($page - 1) * 5 <= $i && $i <= ($page - 1) * 5 + 4){

									$i1 = $i + 1;
									$sender->sendMessage("".$i1."> ".$a." → ".$b."".$this->unit);
								}
								$i++;
						}
						return true;
						break;

					case "throw":
						if($check === false){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-console")."");
							return true;
						}
						if(!isset($args[1]) || !is_numeric($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}

						$message = $this->y->getData("command-throw");
						$sender->sendMessage(str_replace(array('%a', '%b'), array($args[1], $this->unit),  MoneyPlusAPI::Prefix.$message));
						$this->y->takeMoney($sender->getName(), $args[1]);
						return true;
						break;

					case "give":
						if(!$sender->isOp()){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-per")."");
							return false;
		
						}elseif(!isset($args[2]) || !is_numeric($args[2])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}elseif(!$this->y->exist($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-not")."");
							return true;
						}

						$message = $this->y->getData("command-give");
						$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  MoneyPlusAPI::Prefix.$message));
						$this->y->addMoney($args[1], $args[2]);
						return true;
						break;

					case "take":
						if(!$sender->isOp()){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-per")."");
							return false;

						}elseif(!isset($args[2]) || !is_numeric($args[2])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}elseif(!$this->y->exist($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-not")."");
							return true;
						}

						$message = $this->y->getData("command-take");
						$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  MoneyPlusAPI::Prefix.$message));
						$this->y->takeMoney($args[1], $args[2]);
						return true;
						break;

					case "set":
						if(!$sender->isOp()){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-per")."");
							return false;
 		
						}elseif(!isset($args[2]) || !is_numeric($args[2])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-type")."");
							return true;
						}elseif(!$this->y->exist($args[1])){
							$sender->sendMessage(MoneyPlusAPI::Prefix.$this->y->getData("error-not")."");
							return true;
						}

						$message = $this->y->getData("command-set");
						$sender->sendMessage(str_replace(array('%p', '%a', '%b'), array($args[1], $args[2], $this->unit),  MoneyPlusAPI::Prefix.$message));
						$this->y->setMoney($args[1], $args[2]);
						return true;
						break;

					case "help":
						$a = $this->y->getData("help");
						$sender->sendMessage("§l§e[ §bMoneyPlus's help §e]");
						foreach($a as $aho => $b){
							$sender->sendMessage("".$b."");
						}
						return true;
						break;

					default:
						$a = $this->y->getData("help");
						$sender->sendMessage("§l§e[ MoneyPlus's help ]");
						foreach($a as $aho => $b){
							$sender->sendMessage("".$b."");
						}
						return true;
						break;
				}//subcommand switch
			break;
		}//command switch
	}//command function
	


//外部使用用API群

/*所持金取得*/
	public function getMoney(String $name){
		return $this->y->getMoney($name);
	}

/*所持金増やす*/	
	public function addMoney(String $name, int $price){
		$this->y->addMoney($name, $price);
		
	}

/*所持金減額wwwwwww*/
	public function takeMoney(String $name, int $price){
		$this->y->takeMoney($name, $price);
	}

/*所持金設定*/
	public function setMoney(String $name, int $price){
		$this->y->setMoney($name, $price);
	}

/*データがあるか確認*/
	public function exist(string $name){
		return $this->y->exist($name);
	}

/*初回入室時などの際に登録*/
	public function setPlayerData(String $name){
		$this->y->setPlayerData($name);
	}
	
/*便利な 設定取得関数*/
	public function getUnit(){
		return $this->y->getData("unit");
	}

	public function getDefaultMoney(){
		return $this->y->getData("default-money");
	}

	public function getAllMoney(){
		return $this->y->getAllMoney();
	}


}

