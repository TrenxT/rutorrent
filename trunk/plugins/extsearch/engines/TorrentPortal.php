<?php

class TorrentPortalEngine extends commonEngine
{
	public function action($what,$cat,&$ret,$limit)
	{
		$added = 0;
		$url = 'http://www.torrentportal.com';
		$categories = array( 'all'=>'0', 'movies'=>'2', 'tv'=>'3', 'music'=>'7', 'games'=>'1', 'anime'=>'6', 'software'=>'5', 'pictures'=>'8', 'books'=>'9' );
		if(!array_key_exists($cat,$categories))
			$cat = 'all';
		for($pg = 0; $pg<10; $pg++)
		{
			$cli = self::fetch( $url.'/torrents-search.php?search='.$what.'&cat='.$categories[$cat].'&sort=seeders&d=desc&type=and&hidedead=on&page='.$pg );
			if( ($cli==false) || (strpos($cli->results, "<b>No Torrents Found</b>")!==false) )
				break;
			$res = preg_match_all('/<a href="\/download\/(?P<id>\d+)\/.*<a href="torrents.php\\?cat=\d{1,2}">(?P<cat>.*)<\/a>.*<b>(?P<name>.*)<\/b><\/a><\/td><td .*>.*<\/td><td .*>(?P<size>.*)<\/td><td .*>(?P<seeds>.*)<\/td><td .*>(?P<leech>.*)<\/td>/siU', $cli->results, $matches);
			if(($res!==false) && ($res>0) &&
				count($matches["id"])==count($matches["cat"]) &&
				count($matches["cat"])==count($matches["name"]) && 
				count($matches["name"])==count($matches["size"]) &&
				count($matches["size"])==count($matches["seeds"]) &&
				count($matches["seeds"])==count($matches["leech"]) )
			{
				for($i=0; $i<count($matches["id"]); $i++)
				{
					$link = $url."/download/".$matches["id"][$i];
					if(!array_key_exists($link,$ret))
					{
						$item = $this->getNewEntry();
						$item["cat"] = self::removeTags($matches["cat"][$i]);
						$item["desc"] = $url."/details/".$matches["id"][$i];
						$item["name"] = self::removeTags($matches["name"][$i]);
						$item["size"] = self::formatSize($matches["size"][$i]);
						$item["seeds"] = intval(self::removeTags($matches["seeds"][$i]));
						$item["peers"] = intval(self::removeTags($matches["leech"][$i]));
						$ret[$link] = $item;
						$added++;
						if($added>=$limit)
							return;
					}
				}
			}
			else
				break;
		}
	}
}

?>