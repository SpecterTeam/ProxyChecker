<?php
/**
 *     ProxyChecker  Copyright (C) 2018  SpecterTeam
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace friscowz\proxychecker;


use friscowz\proxychecker\task\ProxyCheckerTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class ProxyChecker extends PluginBase implements Listener
{
    /** @var Config */
    public static $config;
    /** @var ProxyChecker */
    public static $instance;

    public function onEnable()
    {
        self::setInstance($this);
        if(!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
        $readme = "This plugin is made by @OGFris (github.com/OGFris), the plugin uses an Online API also made by @OGFris to check the IP." . PHP_EOL . " If you want the process to be 3 times FASTER, contact @OGFris on twitter or email support@legacyhcf.net to buy a license key as cheap as $10 for lifetime!";
        if (file_exists($this->getDataFolder() . "readme.txt")){
            if(file_get_contents($this->getDataFolder() . "readme.txt") != $readme){
                file_put_contents($this->getDataFolder() . "readme.txt", $readme);
            }
        } else {
            $f = fopen($this->getDataFolder() . "readme.txt", "w");
            fwrite($f, $readme);
            fclose($f);
        }
        if (!file_exists($this->getDataFolder() . "config.yml")){
            $config = new Config($this->getDataFolder() . "config.ml", Config::YAML, ["key" => "null", "kick" => true, "ban" => false, "kick-message" => "You can't use Proxies/VPN on this server because it's protected with ProxyChecker.ga !"]);
            self::setConfigData($config);
        }
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "Plugin loaded successfully ! made by @OGFris (github.com/OGFris)");
    }

    /**
     * @return Config
     */
    public static function getConfigData() : Config
    {
        return self::$config;
    }

    /**
     * @param Config $config
     */
    public static function setConfigData(Config $config)
    {
        self::$config = $config;
    }

    /**
     * @return ProxyChecker
     */
    public static function getInstance() : ProxyChecker
    {
        return self::$instance;
    }

    /**
     * @param ProxyChecker $instance
     */
    public static function setInstance(ProxyChecker $instance)
    {
        self::$instance = $instance;
    }

    /**
     * @param PlayerPreLoginEvent $event
     */
    public function onPreLogin(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayer();
        $ip = $player->getAddress();
        $name = $player->getName();
        Server::getInstance()->getAsyncPool()->submitTask(new ProxyCheckerTask($ip, $name, self::getConfigData()->get("key")));
    }
}
