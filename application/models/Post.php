<?php

class Post extends BusinessObject
{
    private $_id;

    public function __construct($id = null)
    {
        // instantiating this object does not load any data
        $this->_id = $id;
    }

    public function load()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        $sql = "select * from posts where post_id = ? limit 1";

        $data = $this->_db()->getRow($sql, array($this->_id));

        $data['title'] = TextUtilities::escape($data['title']);
        $data['body'] = TextUtilities::escape($data['body']);

        return $data;
    }

    // since getTime() requires special logic, we can override this behavior here
    public function getTime()
    {
        return strtotime($this->create_dt_tm);
    }

    public function getComments()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        $sql =  "select comment_id from comments where post_id = ?";

        // must set the type of returned objects
        $this->_hint('Post_Comment');

        return $this->_db()->getCol($sql, array($this->_id));
    }

    public function delete()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        // i guess we should delete comments of a post too
        $db = $this->_db();

        $db->startTransaction();

        $sql =  "delete from comments where post_id = ?";
        $ret = $db->query($sql, array($this->_id));

        if ($ret == true)
        {
            $sql =  "delete from posts where post_id = ?";
            $ret = $db->query($sql, array($this->_id));
        }

        return ($ret == true) ? $db->commit() : $db->rollback();
    }

    public function save()
    {
        $this->_logger()->debug(__CLASS__ . '::' . __METHOD__);

        $sql = "insert into posts
                    (post_id, title, body, create_dt_tm)
                    values
                    (?, ?, ?, ?)
                    on duplicate key update
                    title = ?,
                    body = ?";

        $bind = array($this->_id,
                      $this->title,
                      $this->body, 
                      $this->create_dt_tm,
                      $this->title, 
                      $this->body);

        $this->_db()->query($sql, $bind);
    }
}

