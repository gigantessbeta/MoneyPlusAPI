<?php
namespace MP\DataAccess;

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

use MP\MoneyPlusAPI;

use MP\event\MoneyChangeEvent;
use MP\event\UserRegisterEvent;
use MP\event\UserUnregisterEvent;

use MP\DataAccess\FunctionConnectManager;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;


class YamlManager extends PluginBase implements FunctionConnectManager{

	public $c, $money, $m;

	public function __construct(MoneyPlusAPI $m){
		$this->m = $m;
		if(!file_exists($this->m->getDataFolder())){
			mkdir($this->m->getDataFolder(), 0744, true);
		}
		$this->money = new Config($this->m->getDataFolder() . "money.yml", Config::YAML);
		$this->c = new Config($this->m->getDataFolder() . "config.yml", Config::YAML, array(
			'config-version' => '2',
			'unit' => 'MP',
			'max-money' => '1000000000',
			'default-money' => '500',
			'ranking-op-enable' => 'false',
			'register' => 'あなたのデータを登録しました。',
			'money-received' => '%pさんから%a%b受け取りました。',
			'command-check' => 'あなたの所持金: %a%b',
			'command-view' => '%p の所持金: %a%b',
			'command-pay' => '%p に %a%b 支払いました。',
			'command-rank' => '所持金ランキング',
			'command-rankme' => 'あなたは所持金ランキング %l 位です。',
			'command-throw' => '%a%b 捨てました。',
			'command-give' => '%p の所持金を %a%b 増やしました。',
			'command-take' => '%p の所持金を %a%b 減らしました。',
			'command-set' => '%p の所持金を %a%b に設定しました。',
			'error-type' => '§c正しい形式で入力してください。/m help',
			'error-not' => '§cそのプレイヤーは存在しません。',
			'error-console' => '§cコンソールからは実行できません。',
			'error-money' => '§c所持金が足りません。',
			'error-per' => '§c権限者専用のコマンドです。',
			'error-rankmeop' => '§cあなたはランキング対象外です。',
			'help' => array(
				'help-check' => '/m check : 所持金確認',
				'help-view' => '/m view {プレイヤー} : プレイヤーの所持金確認',
				'help-pay' => '/m pay {プレイヤー} {金額} : プレイヤーに金額支払い',
				'help-rank' => '/m rank {ページ数} : 所持金ランキング',
				'help-rankme' => '/m rankme : 自分の所持金ランキングが何位か表示',
				'help-throw' => '/m throw {金額} : 金額分所持金を捨てます。'
				)
		));
	}

/*所持金取得*/
	public function getMoney(String $name){
		$name = strtolower($name);
		if($this->money->exists($name)){
			return $this->money->get($name);

		}else{
			return false;
		}

	}

/*所持金増やす*/	
	public function addMoney(String $name, int $price, $case){
		$name = strtolower($name);
		if(!$this->money->exists($name)){
			return false;
		}
		$hand = $this->money->get($name);
		$result = $hand + $price;

		if($this->c->get("max-money") < $result){
			$result = $this->c->get("max-money");
		}

		$this->m->getServer()->getPluginManager()->callEvent($event = new MoneyChangeEvent($name, $hand, $result, $case));
	
		if(!$event->isCancelled()){
			$this->setValue($name, $result);
		}
		return true;
	}

/*所持金減額wwwwwww*/
	public function takeMoney(String $name, int $price, $case){
		$name = strtolower($name);
		if(!$this->money->exists($name)){
			return false;
		}
		$hand = $this->money->get($name);
		$result = $hand - $price;

		if(0 > $result){
			$result = 0;
		}

		$this->m->getServer()->getPluginManager()->callEvent($event = new MoneyChangeEvent($name, $hand, $result, $case));

		if(!$event->isCancelled()){
			$this->setValue($name, $result);
		}
			return true;
	}

/*所持金設定*/
	public function setMoney(String $name, int $price, $case){
		$name = strtolower($name);
		if(!$this->money->exists($name)){
			return false;
		}
		$hand = $this->money->get($name);
		if(0 > $price){
			$price = 0;
		}

		$this->m->getServer()->getPluginManager()->callEvent($event = new MoneyChangeEvent($name, $hand, $price, $case));

		if(!$event->isCancelled()){
			$this->setValue($name, $price);
		}

	}

/*データがあるか確認*/
	public function exist(string $name){
		$name = strtolower($name);
	
		return $this->money->exists($name);
	}

/*初回入室時などの際に登録*/
	public function setPlayerData(String $name, $case){
		$name = strtolower($name);
		$defmoney = $this->c->get("default-money");
		
		$this->m->getServer()->getPluginManager()->callEvent($event = new UserRegisterEvent($name, $defmoney, $case));

		if(!$event->isCancelled()){
			$this->setValue($name, $defmoney);
		}
		return true;
	}

	public function removePlayerData(String $name, $case){
		$name = strtolower($name);
		if(!$this->money->exists($name)){
			return false;
		}
		$money = $this->money->get($name);
		$this->m->getServer()->getPluginManager()->callEvent($event = new UserUnregisterEvent($name, $money, $case));

		if(!$event->isCancelled()){
			$this->money->remove($name);
			$this->money->save();
		}
		return true;
	}

/*取得関係*/
	public function getData(String $key){
		if(!$this->c->exists($key)){
			return "An error occurred in config. Please review the file.";
		}
		return $this->c->get($key);
	}

	public function getAllMoney(){
		return $this->money->getAll();
	}


	public function setValue(String $name,Int $money){
		$this->money->set($name, $money);
		$this->money->save();
	}
}