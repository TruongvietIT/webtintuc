<?php

class CategoryModel extends Model
{
    public function getCategory()
    {
        $key = __FUNCTION__;
//        if ($result = $this->getCacheData($key)) {
//            return $result;
//        }

        $sql = "SELECT * FROM category";
        $sql = $this->_conn->execute($sql);
        $result = $this->_conn->fetchAll($sql);

//        $this->setCacheData($key, $result);
        return $result;
    }

}
