<?php

namespace Vanic\Cosmetics\Command;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\player\Player;
use Vanic\Cosmetics\Main;
use Vanic\Cosmetics\Utils\SkinUtils;

class CapesCommand extends Command {
  
  private Main $main;
  
  public function __construct(string $name, Main $plugin, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
    $this->main = $plugin;
    parent::__construct($name, $description, $usageMessage, $aliases);
    $this->setPermission('cosmetics.capes');
  }
  
  /**
   * @inheritDoc
   */
  
  public function execute(CommandSender $sender, string $commandLabel, array $args) {
    if ($sender instanceof Player) {
      $form = new SimpleForm(function (Player $player, int $data = null) {
        if ($data === null) return true;
        if($data == 0){
          $this->main->getPlayerDataFile()->set($player->getName(), [$this->main->getPlayerDataFile()->get($player->getName())[0], ""]);
          $this->main->getPlayerDataFile()->save();
          $player->setSkin(SkinUtils::getResetSkin($player->getSkin()));
          $player->setSkin(SkinUtils::layerSkin($player->getSkin(), $this->main->getPlayerDataFile()->get($player->getName())[0]));
          $player->sendSkin();
          $player->sendMessage($this->main->getMessagesConfig()->get('prefix') . $this->main->getMessagesConfig()->get('unequip'));
        }else {
          $permission = $this->main->getCapes()[$data - 1]['permission'];
          if ($player->hasPermission($permission)) {
            $this->main->getPlayerDataFile()->set($player->getName(), [$this->main->getPlayerDataFile()->get($player->getName())[0], $this->main->getCapes()[$data - 1]['name']]);
            $this->main->getPlayerDataFile()->save();
            $player->setSkin(SkinUtils::getCapeSkin($player->getSkin(), $this->main->getCapes()[$data - 1]['name']));
            $player->sendSkin();
            $player->sendMessage($this->main->getMessagesConfig()->get('prefix') . str_replace("{display-name}", $this->main->getCapes()[$data - 1]['display-name'], $this->main->getMessagesConfig()->get('equip')));
          } else {
            $player->sendMessage($this->main->getMessagesConfig()->get('prefix') . str_replace("{display-name}", $this->main->getCapes()[$data - 1]['display-name'], $this->main->getMessagesConfig()->get('no-permission')));
          }
        }
        return false;
      });
      
      $form->setTitle("§5§lCAPES MENU");
      $form->addButton("NONE");
      foreach ($this->main->getCapes() as $cape) {
        $displayname = $cape["display-name"];
        $permission = $cape["permission"];
        $permissiontext = $sender->hasPermission($permission) ? "§2[UNLOCKED]" : "§4[LOCKED]";
        $form->addButton("$displayname\n §r" . $permissiontext);
      }
      $form->sendToPlayer($sender);
    }
  }
}