<?php

//使えるところあったらどんどんコピペしてね♡

namespace xanadu2875\imagelib;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\{Config, Color, Utils};
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\nbt\tag\StringTag;
use pocketmine\event\{Listener, player\PlayerJoinEvent};
define("RAND_MAX", 1000000);
define("WIDTH", 128);
define("HEIGHT", 128);
define("SCALE", 1);

class ImageLib extends PluginBase implements Listener
{
  private static $plugin;
  public $images = [];

  public function onLoad()
  {
    if(!$this->checkUpdata()) { $this->getServer()->getLogger()->notice("新しいバージョンがリリースされています。(" . $this->getDescription()->getWebsite() . ")"); }

    self::$plugin = $this;

    @mkdir($this->getDataFolder(), 777);
    @mkdir($this->getDataFolder() . "image/", 777);

    $config = ($source = new Config($this->getDataFolder() . "images.yml", Config::YAML))->getAll();
    $images = glob($this->getDataFolder() . "image/{*.png,*.jpg,*.jpeg}", GLOB_BRACE);

    foreach($images as $key => $filename)
    {
      if(!isset($config[$filename]))
      {
        $config[$filename] = mt_rand(0, RAND_MAX);
      }
    }

    foreach($config as $key0 => $value)
    {
      $result = false;

      foreach($images as $key1 => $filename)
      {
        if($filename === $key0)
        {
          $result = true;
          unset($images[$key1]);
          break;
        }
      }

      if(!$result)
      {
        unset($config[$key0]);
      }
    }

    $source->setAll($config);
    $source->save();

    foreach($config as $image => $id)
    {
      $colors = [];

      switch(strtolower(pathinfo($image)["extension"]))
      {
        case "png":
          if(!$image = @imagecreatefrompng($image)) { break; }
          break;
        case "jpg":
        case "jpeg":
          if(!$image = @imagecreatefromjpeg($image)) { break; }
          break;
        default:
          break;
      }

      $image = imagescale($image, WIDTH, HEIGHT, IMG_NEAREST_NEIGHBOUR);

      for($y = 0;$y < WIDTH; ++$y)
      {
        for($x = 0;$x < HEIGHT; ++$x)
        {
          $rgb = imagecolorsforindex($image, imagecolorat($image, $x, $y));
          $colors[$y][$x] = new Color($rgb["red"], $rgb["green"], $rgb["blue"], 0xff);
        }
      }

      $pk = new ClientboundMapItemDataPacket();
      $pk->mapId = $id;
      $pk->type = ClientboundMapItemDataPacket::BITFLAG_TEXTURE_UPDATE;
      $pk->height = HEIGHT;
      $pk->width = WIDTH;
      $pk->scale = SCALE;
      $pk->colors = $colors;

      $this->images[(int)$id] = $pk;
    }
  }

  public function onEnable() { $this->getServer()->getPluginManager()->registerEvents($this, $this); }

  public static function getInstance() : PluginBase { return self::$plugin; }

  private function checkUpdata() : bool { return str_replace("\n", "",Utils::getURL("https://raw.githubusercontent.com/Xanadu2875/VersionManager/master/ImageLib.txt" . '?' . time() . mt_rand())) === $this->getDescription()->getVersion(); }

  public function getMap(int $id) : Item
  {
    if(isset($this->images[$id]))
    {
      $item = Item::get(Item::FILLED_MAP, 0, 1);
      $tag = $item->getNamedTag();
      $tag->map_uuid = new StringTag("map_uuid", $id);
      $item->setNamedTag($tag);
      return $item;
    }
    else { return Item::get(Item::AIR, 0, 0); }
  }

  public function onJoin(PlayerJoinEvent $event)
  {
    $player = $event->getPlayer();
    foreach($this->images as $pk) { $player->dataPacket($pk); }
  }
}
