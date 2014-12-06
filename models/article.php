<?php

class Article
{

    protected $url;
    protected $title;
    protected $category;
    protected $publish_datetime;

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getPublishDatetime($format = null)
    {
        if ($format === null) {
            return $this->publish_datetime;

        } else {
            return date($format, $this->publish_datetime);

        }
    }

    /**
     * @param mixed $publish_datetime
     */
    public function setPublishDatetime($publish_datetime)
    {
        $this->publish_datetime = strtotime($publish_datetime);
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


}