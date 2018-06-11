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

namespace friscowz\proxychecker\task;


use friscowz\proxychecker\ProxyChecker;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ProxyCheckerTask extends AsyncTask
{
    /** @var string */
    private $ip;
    /** @var string */
    private $player;
    /** @var string */
    private $key;

    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun()
    {
        $result = json_decode(file_get_contents("https://proxychecker-web.herokuapp.com/?ip=" . $this->getIp() . "&key=" . $this->getKey()));
        $this->setResult($result);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server)
    {
        $results = $this->getResult();
        switch($results["status"]){
            case 0:
                //Proxy detected!
                $server->getLogger()->debug(TextFormat::RED . $this->getPlayer() . " is using a Proxy/VPN!");
                $player = $server->getPlayer($this->getPlayer());
                if ($player){
                    if (ProxyChecker::getConfigData()->get("kick") == true){
                        $player->kick(ProxyChecker::getConfigData()->get("kick-message"), false);
                    }
                    if (ProxyChecker::getConfigData()->get("ban") == true){
                        $player->setBanned(true);
                    }
                }
            break;

            case 1:
                //No Proxy detected!
                $server->getLogger()->debug(TextFormat::GREEN . $this->getPlayer() . " isn't using a Proxy/VPN!");
            break;

            case 2:
                //Error!
                $server->getLogger()->info(TextFormat::RED . "an error has occurred while trying to check " . $this->getPlayer() . "'s IP! Please make you sure to report this error on our github: github.com/SpecterTeam.");
            break;
        }
    }

    /**
     * ProxyCheckerTask constructor.
     * @param string $ip
     * @param string $player
     * @param string $key
     */
    public function __construct(string $ip, string $player, string $key = "null")
    {
        $this->setIp($ip);
        $this->setPlayer($player);
        $this->setKey($key);
    }

    /**
     * @return string
     */
    public function getPlayer(): string
    {
        return $this->player;
    }

    /**
     * @param string $player
     */
    public function setPlayer(string $player)
    {
        $this->player = $player;
    }

    /**
     * @return string
     */
    public function getIp() : string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getKey() : string
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }
}
