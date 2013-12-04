<?php
/**
 * Get mod rewrite rules from a xml file.
 *
 * Get a xml file (exported from wordpress),
 * create a new file only with the links from the xml file
 * and create another file with RedirecMatch 301.
 *
 * It's used by generateRewriteRules.sh.
 * To use in terminal:
 * sh parseXmlScript.sh 'pathToXml' 'newDomain'
 *
 * @param $xmlPath String, path to Xml
 * @param $newDomain String, new domain with http;//
 */
function getModRewriteRules($xmlPath, $newDomain)
{
    $xmlFile = simplexml_load_file($xmlPath);

    $xmlChannel = $xmlFile->channel;

    $linkFilePath = __DIR__."/links.txt";

    file_put_contents($linkFilePath, '');

    foreach($xmlChannel->item as $item)
    {
        $postType = $item->children('http://wordpress.org/export/1.2/')->post_type;
        if ($postType == 'post')
        {
            $link = $item->link;
            $pathPatter = "/^(https?):(\/\/([a-z0-9+\$_-]\.?)+)*\/?[0-9]+\/?[0-9]+/";
            preg_match($pathPatter, $link, $matches);
            if (sizeof($matches) > 0 )
            {
                $links = $link . "\n";
                file_put_contents($linkFilePath, $links, FILE_APPEND);
            }
        }
    }

    $fileLinks = file($linkFilePath, FILE_IGNORE_NEW_LINES);
    $rewriteFilePath = __DIR__."/rewrite.txt";

    file_put_contents($rewriteFilePath, '');

    foreach($fileLinks as $link)
    {
        $parseUrl = parse_url($link);
        $path = $parseUrl['path'];

        $pathExplode = explode('/', $path);

        $rewrite = "RedirectMatch 301 ^(.*)/" . $pathExplode[3] . "/(.*) " . $newDomain . $path . "\n";

        file_put_contents($rewriteFilePath, $rewrite, FILE_APPEND);
    }
}







