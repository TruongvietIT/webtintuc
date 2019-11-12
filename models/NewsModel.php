<?php


class NewsModel extends Model
{
    protected $_newsIds = array();

    public function getNews($options = array(), $pageIndex = 1, $pageSize = 10)
    {
        if (isset($options['name_like']) && !empty($options['name_like'])) {
            $options['name_like'] = '%' . $options['name_like'] . '%';
        }
        if (isset($options['news_id'])) {
            $key = __FUNCTION__ . '.' . $options['news_id'];
        } else {
            $key = __FUNCTION__ . '.' . implode('.', $options) . '.' . $pageIndex . '.' . $pageSize;

            if (!isset($options['others'])) {
                $options['others'] = implode(',', $this->_newsIds);
            }
        }

        $result = $this->getCacheData($key);
        if (!empty($result)) {
            if ((isset($options['news_id']) && isset($result['news_status']) && $result['news_status'] == 1) || !isset($options['news_id'])) {
                return $result;
            }
        }

        $sql = 'SELECT  n.news_id,
						n.news_title,
						n.news_sapo,
						n.news_image,
						n.source_name,
						n.visit_count,
						n.news_status,
                                                n.news_tag,
                                                n.news_tag_slug,
						n.news_published_date,
						n.news_modified_date,
						n.news_relation,
						n.news_slug,
                                                n.category_id,
						n.is_video,
						n.is_outside,
                                                n.is_sensitive,
						n.news_redirect_link,
						n.news_seo_title,
						n.news_seo_description,
						n.news_seo_keyword' .
            (isset($options['get_content']) && $options['get_content'] == 1 ? ',n.news_content' : '') .
            //(isset($options['news_id']) ? ',n.news_content, n.news_tag' : '') .
            (isset($options['event_id']) ? ',ne.event_id, ne.event_name, ne.event_image' : '') .
            ' FROM news AS n ' .
            (isset($options['event_id']) ? ' INNER JOIN news_event_rel AS ner ON ner.news_id = n.news_id
												        INNER JOIN news_event AS ne on ne.event_id = ner.event_id' : '') .
            (isset($options['position_id']) ? ' INNER JOIN news_position_rel AS npr ON npr.news_id = n.news_id ' : '') .
            ' WHERE 1=1 ' .
            (isset($options['news_id']) ? ' AND n.news_id = :news_id ' : '') .
            (isset($options['news_ids']) ? ' AND n.news_id IN (' . $options['news_ids'] . ') ' : '') .
            (isset($options['position_id']) ? ' AND npr.position_id = :position_id ' : '') .
            (isset($options['status']) ? ' AND n.news_status = :status ' : '') .
            (isset($options['category_id']) ? ' AND n.category_id = :category_id ' : '') .
            (isset($options['others']) && !empty($options['others']) ? ' AND n.news_id NOT IN (' . $options['others'] . ') ' : '') .
            //to check mode preview or not
            (isset($options['published_date']) ? ' AND n.news_published_date >= ' . $options['published_date'] . ' AND n.news_published_date <= ' . (intval($options['published_date']) + 86400) : '') .
            (!isset($options['mode']) ? ' AND n.news_published_date <= ' . (time() + 60) : '') .
            ' ORDER BY ' .
            (isset($options['order_by']) ? ' n.' . $options['order_by'] : ' n.news_published_date') .
            (isset($options['order_asc']) ? '' : ' DESC') .
            ' LIMIT ' . (($pageIndex - 1) * $pageSize) . ',' . $pageSize;

        $sql = $this->_conn->prepareSQL($sql, $options);

//        echo $sql;

        $result = $pageSize == 1 || isset($options['news_id']) ? array($this->_conn->fetchRow($sql)) : $this->_conn->fetchAll($sql);
//        $result = $this->_conn->fetchAll($sql);
        if (!empty($result)) {
            foreach ($result as &$item) {
                if (isset($item['news_id']) && !empty($item['news_id'])) {
                    if ($options)
                        array_push($this->_newsIds, $item['news_id']);
                    $item['news_title'] = stripslashes($item['news_title']);

                    $item['news_link'] = Context::getInstance()->getRoute()->createUrl('NewsDetail', array('slug' => isset($item['news_slug']) && !empty($item['news_slug']) ? $item['news_slug'] : Util::toUnsign($item['news_title']),
                        'id' => $item['news_id']));

                    if (isset($options['get_related']) && isset($item['news_relation']) && !empty($item['news_relation'])) {
                        $item['related_news'] = $this->getNews(array('news_ids' => $item['news_relation'],
                            'others' => 0,
                            'status' => 1), 1, 10);
                    }
                }
            }

            if ($pageSize == 1 || isset($options['news_id'])) {
                $result = $result[0];
            }
        }
        return $result;
    }

    public function getNewNews()
    {
        $key = __FUNCTION__;
        if ($result = $this->getCacheData($key)) {
            return $result;
        }

        $result = $this->getNews(array('news_status' => 1, 'order_by' => 'news_published_date'), 1, 7);

//        var_dump($result);
        $this->setCache($key, $result);

        return $result;

    }

    public function getNewsDetail($id, $mode = '')
    {
        // update visit count
        $key = 'NEWS_VISIT_COUNT';
        $visits = $this->getCacheData($key);
        $keyFlag = 'NEWS_VISIT_COUNT_FLAG';
        $visits[$id] = isset($visits[$id]) ? $visits[$id] + 1 : 1;
        if ($this->getCacheData($keyFlag)) {
            $this->setCacheData($key, $visits);
        }

        $key = __FUNCTION__ . '.' . $id;
        if ($mode == 'preview') {
            $this->setCacheActive(false);
            $news = $this->getNews(array('news_id' => $id,
                'get_content' => 1,
                'mode' => 'preview'));
        } else {
            if ($result = $this->getCacheData($key)) {
                if (isset($result['news_id']) && isset($result['news_status']) && $result['news_status'] == '1') {
                    return $result;
                }
            }
            $news = $this->getNews(array('news_id' => $id,
                'get_content' => 1,
                'status' => 1,
                'get_related' => 1));
        }

        if (isset($news['news_content'])) {
            // Ảnh bài chi tiết resize về cỡ 580px
            $news['news_content'] = preg_replace_callback('#<img[^>]+src="(http://webtintuc.local/[^"]+)"[^>]*>#', create_function('$m', 'return str_replace($m[1], Util::getThumbSrc($m[1], 580, 1100), $m[0]);'), $news['news_content']);

            $news['category'] = $this->getCategory(['category_id' => isset($news['category_id']) ? $news['category_id'] : 0]);
        }

        $news['relates'] = $this->getNews(array('news_ids' => isset($news['news_relation']) ? $news['news_relation'] : 0,
            'others' => 0,
            'status' => 1), 1, 10);

        //if ($mode != 'preview'){
        $this->setCacheData($key, $news);
        //}
//        echo "<pre>";
//        var_dump($news[0]);
        return $news;
    }
    public function getCategory($options = array(), $limit = 0)
    {
        $key = __FUNCTION__ . '.' . implode('.', $options) . $limit;
        if ($result = $this->getCacheData($key)) {
            return $result;
        }
        if (isset($options['header_order'])) {
            $condition = 'AND category_header_order > 0';
            $orderBy = 'category_header_order';
        } elseif (isset($options['home_order'])) {
            $condition = 'AND category_home_order > 0';
            $orderBy = 'category_home_order';
        } elseif (isset($options['footer_order'])) {
            $condition = 'AND category_footer_order > 0';
            $orderBy = 'category_footer_order';
        } else {
            $orderBy = 'category_id DESC';
        }
        $sql = 'SELECT * FROM category WHERE 1=1 ' .
            (isset($condition) ? $condition : '') .
            (isset($options['parent_id']) ? ' AND parent_id           = :parent_id' : '') .
            (isset($options['category_id']) ? ' AND category_id         = :category_id' : '') .
            (isset($options['category_ids']) ? ' AND category_id IN (' . $options['ids'] . ')' : '') .
            (isset($options['template']) ? ' AND category_template   = :template' : '') .
            (isset($options['category_status']) ? ' AND category_status     = :category_status' : ' AND category_status = 1') .
            (isset($options['category_slug']) ? ' AND category_slug       = :category_slug' : '') .
            ' ORDER BY ' . $orderBy .
            ($limit > 0 ? ' LIMIT ' . $limit : '');
        $sql = $this->_conn->prepareSQL($sql, $options);
        $result = $limit == 1 || isset($options['category_id']) ? array($this->_conn->fetchRow($sql)) : $this->_conn->fetchAll($sql);
        if (!empty($result)) {
            $route = Context::getInstance()->getRoute();
            foreach ($result as &$item) {
                $item['category_link'] = $route->createUrl('Category', array('slug' => isset($item['category_slug']) && !empty($item['category_slug']) ? $item['category_slug'] : ''));
                $item['category_link'] = trim($item['category_link'], '/') . '/';
            }
            if ($limit == 1 || isset($options['category_id'])) {
                $result = $result[0];
            }
        }

        $this->setCacheData($key, $result);
        return $result;
    }

    public function getCategoryNews($options = array(), $pageIndex = 1, $pageSize = 8)
    {
        $key = __FUNCTION__ . implode('.', $options) . $pageIndex . $pageSize;
        if ($result = $this->getCacheData($key)) {
            return $result;
        }
        $parentId = 0;
        //options parent: slug
        if (isset($options['parent_slug']) && !empty($options['parent_slug'])) {
            $parent = $this->getCategory(array('category_slug' => $options['parent_slug']), 1);
            if (!empty($parent)) {
                $parentId = $parent['category_id'];
            }
        }
        if ($category = $this->getCategory(array('category_slug' => $options['category_slug'],
            'parent_id' => $parentId), 1)) {
            $listNews = $this->getNews(array('category_id' => $category['category_id'],
                'others' => 0,
                'status' => 1), $pageIndex, $pageSize);
            $result = array(
                'category' => $category,
                'parent' => isset($parent) && !empty($parent) ? $parent : null,
                'news' => $listNews
            );
        }

        $this->setCacheData($key, $result);
        return $result;
    }

    public function getHeaderCategory()
    {
        $key = __FUNCTION__; // $key == getHeaderCategory
        if ($result = $this->getCacheData($key)) {
            return $result;
        }

        $result = $this->getCategory(array('header_order' => 1, 'parent_id' => 0, 'category_status' => 1));

        $this->setCacheData($key, $result);
        return $result;
    }


}