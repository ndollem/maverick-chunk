<?php

use League\HTMLToMarkdown\HtmlConverter;

class maverickChunker
{

    private $content;

    function __construct($rawContent)
    {
        $this->content = ($rawContent) ? $rawContent : false;
    }

    public function parseNews($row)
    {
        $news = collect();

        $news = $news->merge($row['news_content']);

        if(count($row['news_paging']) > 0) {
            foreach($row['news_paging'] as $np) {
                $news = $news->merge(html_entity_decode($np['content']));
            }
        }

        $this->content = $news;

        $news = $this->explode_content();

        return json_decode($news, true);
    }

    function explode_content()
    {
        if ($this->content) {

            $htmlToMarkdown = new HtmlConverter(['strip_tags'=>true]);
            $markdown = $htmlToMarkdown->convert($this->content);
            //echo $markdown; exit;
            $paragraphs = explode("\n", $markdown);
            $describedParagraph = [];
            foreach ($paragraphs as $paragraph) {
                if (trim($paragraph) != '') {
                    $describedParagraph[] = $this->describeParagraph(trim($paragraph));
                }
            }


            $result = [
                'paragraphs' => [
                    'count' => count($describedParagraph),
                    'item' => $describedParagraph
                ],
                'source' => $this->content
            ];


            return $this->convert($result);
        }
    }

    function describeParagraph($paragraph)
    {

        $markdownToHtml = new \cebe\markdown\Markdown();

        $type = 'text';

        if ($paragraph[0] == '#') {
            $type = 'subtitle';
        }

        if ($paragraph[0] == '"') {
            $type = 'quote';
        }

        if ($paragraph[0] == '!') {
            $type = 'image';

            $imgHtml = $markdownToHtml->parse($paragraph);

            $dom = new DOMDocument();
            $dom->loadHTML($imgHtml);
            $img = $dom->getElementsByTagName('img')->item(0);

            return [
                'type' => $type,
                'src' => $img->getAttribute('src'),
                'alt' => $img->getAttribute('alt'),
                'title' => $img->getAttribute('title'),
                'html' => $imgHtml,
                'markdown' => $paragraph
            ];
        }

        $describedSentence = [];
        if ($type == 'text') {
            $sentences = explode('.', $paragraph);
            foreach ($sentences as $sentence) {
                if (trim($sentence) != '') {
                    $describedSentence[] = $this->describeSentence(trim($sentence) . '.');
                }
            }
        }

        return [
            'type' => $type,
            'length' => strlen($paragraph),
            'html' => $markdownToHtml->parse($paragraph),
            'markdown' => $paragraph,
            'sentences' => [
                'count' => count($describedSentence),
                'item' => $describedSentence
            ]
        ];
    }

    function describeSentence($sentence)
    {
        $markdownToHtml = new \cebe\markdown\Markdown();

        return [
            'type' => 'text',
            'length' => strlen($sentence),
            'html' => $markdownToHtml->parse($sentence),
            'markdown' => $sentence,
        ];
    }

    function convert($data)
    {
        $collection = [];
        $skip_next = false;

        foreach($data['paragraphs']['item'] as $index=>$row)
        {
            if($skip_next){

            }

            switch ($row['type']) {
                case 'text':
                    if($row['length'] <= 50){

                        $skip_next = false;
                    }
                    break;

                default:
                    $collection[] = $row;    
            } 
            
        }

        return json_encode($data);
    }
}
