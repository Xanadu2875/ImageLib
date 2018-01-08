<?php

//使えるところあったらどんどんコピペしてね♡

namespace xanadu2875\imagelib;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\{Config, Color};
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;
use pocketmine\nbt\tag\StringTag;
define("RAND_MAX", 1000000);
define("WIDTH", 127);
define("HEIGHT", 127);

class ImageLib extends PluginBase
{
  private $plugin;
  public $images = [];

  public function onLoad()
  {
    $plugin = $this;

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
          if(!$image = @imagecreatefrompng($image)) { continue; }
          break;
        case "jpg":
        case "jpeg":
          if(!$image = @imagecreatefromjpeg($image)) { continue; }
          break;
        default:
          continue;
      }

      var_dump($image);

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
      $pk->scale = 1;
      $pk->colors = $colors;

      $this->images[(int)$id] = $pk;
    }
  }

  public static function getInstance() : PluginBase { return self::$plugin; }

  public function getMap(int $id) : Item
  {
    if(isset($this->$images[$id]))
    {
      $item = Item::get(Item::FILLED_MAP, 0, 1);
      $tag = $item->getNamedTag();
      $tag->map_uuid = new StringTag("map_uuid", $id);
      $item->setNamedTag($tag);
    }
    else { return Item::get(Item::AIR, 0, 0); }
  }
}
