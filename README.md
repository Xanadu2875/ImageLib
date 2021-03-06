# ImageLib.
PMMPプラグインです(Written in PHP)

## Description

簡単に地図に画像を写せます。


## Download

### [![MCBE Forum](https://user-images.githubusercontent.com/34952872/34657908-11932076-f46e-11e7-9cf5-60944846ca12.png)](https://forum.mcbe.jp/resources/33/)

## Demo

![demo1](https://user-images.githubusercontent.com/34952872/34657171-00b1c016-f467-11e7-92e2-b0828d984bec.PNG)
![demo2](https://user-images.githubusercontent.com/34952872/34657205-660767ae-f467-11e7-8c6c-51c54c6ba70a.jpg)

## Usage

プラグイン起動後、`plugins/ImageLib/image/`に画像(*.jpg,*.png)を入れるだけです。自動的に128x128にリサイズされます。
undefine functionとコンソールに表示された場合は、bin/php/php.iniの;extension=php_gd2.dllのコメントアウトを外してください。

## For Developers

`xanadu2875\imagelib\ImageLib::getInstance()` でImageLibの関数にアクセスできます。

| 関数 | パラメーター | 説明 |
| :--: | :---------: | :--: |
| getMap(int $id) : Item | id images.ymlに書かれている作りたい画像のパスと対応する整数(0-1000000) | 画像を映した地図(Item)を返します。なかったら何も返しません(Air) |

例:
```PHP
use xanadu2875\imagelib\ImageLib;
$player->getInventory()->addItem(ImageLib::getInstance()->getMap($id));
```

## Author

<details><summary>Xanadu2875</summary>

Twitter
[@xanadu2875](https://twitter.com/xanadu2875)

Lobi
[1a8ca](https://web.lobi.co/user/1a8ca6d4fdd1d87e0f26c68e18f08de6413f7d36)
</details>

## License

GPLLv3

## TODO

- 画像のサイズを変更できるようにする
- 額縁にハメられるようにする
- コマンドの追加

## Anything Else

- コミットくだしあ
