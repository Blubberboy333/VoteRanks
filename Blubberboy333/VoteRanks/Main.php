<?php

namespace Blubberboy333\VoteRanks;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{
    public function onEnable(){
        $this->saveDefaultConfig();
        $this->players = new Config($this->getDataFolder()."Players.yml", Config::YAML);
        $this->plugin = $this->getConfig()->get("Plugin");
        if($this->getServer()->getPluginManager()->getPlugin("VoteReward") == null){
            $this->getLogger()->info(TextFormat::YELLOW."You don't have VoteReward! It is recomended that you use VoteReward with this plugin!");
        }
        if($this->plugin !== "PureChat" || $this->plugin !== "CustomRanks"){
            $this->getLogger()->info(TextFormat::RED."This plugin is not able to use ".$this->plugin.". You can either use CustomRanks or PureChat!");
        }else{
            if($this->getServer()->getPluginManager()->getPlugin($this->plugin) == null){
                $this->getLogger()->info(TextFormat::RED."You don't have ".$this->plugin."! Please fix the config or get either CustomRanks or PureChat!");
            }else{
                $this->getLogger()->info(TextFormat::YELLOW."Using ".$this->plugin." for the ranks plugin...");
            }
        }
        $this->getLogger()->info(TextFormat::GREEN."Done!");
    }
    
    public function addVote($player){
        if($this->players->get($player) == null){
            $this->players->set($player, 1);
			$this->players->save();
        }else{
            $this->players->set($this->getServer()->getPlayer($player)->getName(), intval($this->players->get($player) + 1));
            $this->players->save();
            $rank = $this->getConfig()->get($this->players->get($player));
            if($rank !== null){
                if($this->getConfig()->get("AdminRank") == "false"){
                    $this->rankUp($player, $this->players->get($player));
                }else{
                    if(!($this->getServer()->getPlayer($player)->isOp(true))){
                        $this->rankUp($player, $this->players->get($player));
                    }
                }
            }
        }
    }
    
    public function rankUp($player, $rank){
        $newRank = $this->getPlugin()->get($rank);
        if($this->getConfig()->get("Plugin") == "PureChat"){
            $command = "setgroup $player $newRank";
            $this->getServer()->dispatchCommand(new ConsoleCommandSender, $command);
        }elseif($this->getConfig()->get("Plugin") == "CustomRanks"){
            $command = "srank $player $newRank";
            $this->getServer()->dispatchCommand(new ConsoleCommandSender, $command);
        }else{
            $this->getLogger()->info(TextFormat::RED.$this->getConfig()->get("Plugin")." doesn't work with this plugin!");
        }
        foreach($this->getConfig()->get("Commands") as $i){
            $this->getServer()->dispatchCommand(new ConsoleCommandSender, str_replace(array("{PLAYER}","{NAME}"), array($this->getServer()->getPlayer($player),$this->getServer()->getPlayer($player)->getName())));
        }
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if(strtolower($command->getName()) == "addvote"){
            if($sender->hasPermission("vr") || $sender->hasPermission("vr.add")){
                if(isset($args[0])){
                    $player = $this->getServer()->getPlayer($args[0]);
                    if($player instanceof Player){
                        $name = $player->getName();
                        $this->addVote($name);
						$sender->sendMessage("You added a vote for ".$name);
                    }else{
                        $sender->sendMessage(TextFormat::BLUE.$player.TextFormat::RESET." isn't online!");
                    }
                }else{
                    $sender->sendMessage(TextFormat::YELLOW."You need to specify a player!");
                    return false;
                }
            }else{
                $sender->sendMessage(TextFormat::RED."You don't have permission to use that command!");
                return true;
            }
        }
    }
}
