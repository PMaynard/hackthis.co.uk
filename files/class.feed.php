<?php
    class feed {
        private $app;

        public function __construct($app) {
            $this->app = $app;
        }

        public function get($last=0, $user_id=null) {
            if (!isset($user_id)) {
                $st = $this->app->db->prepare('SELECT username, feed.user_id, feed.type, feed.item_id, feed.time AS timestamp
                        FROM users_feed feed
                        LEFT JOIN users
                        ON feed.user_id = users.user_id
                        WHERE feed.type != "friend" AND feed.type != "comment_mention" AND feed.time > :last
                        ORDER BY time DESC
                        LIMIT 10');
                $st->bindValue(':last', $last);
                $st->execute();
                $result = $st->fetchAll();
            } else {
                $st = $this->app->db->prepare('SELECT feed.feed_id, feed.user_id, feed.type, feed.item_id, feed.time AS timestamp
                        FROM users_feed feed
                        WHERE user_id = :user_id AND feed.time > :last
                        ORDER BY time DESC');
                $st->bindValue(':last', $last);
                $st->bindValue(':user_id', $user_id);
                $st->execute();
                $result = $st->fetchAll();
            }

            // Loop items, get details and create images
            foreach ($result as &$res) {
                if ($res->type == 'friend') {
                    // status
                    $st = $this->app->db->prepare("SELECT username as username_2
                        FROM users
                        WHERE user_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();
                } else if ($res->type == 'medal') {
                    // label, colour
                    $st = $this->app->db->prepare("SELECT medals.label, medals_colours.colour
                        FROM medals
                        LEFT JOIN medals_colours
                        ON medals.colour_id = medals_colours.colour_id
                        WHERE medal_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();
                } else if ($res->type == 'comment' || $res->type == 'comment_mention') {
                    // uri, title
                    $st = $this->app->db->prepare("SELECT users.username, articles.title, CONCAT(IF(articles.category_id = 0, '/news/', '/articles/'), articles.slug) AS uri
                        FROM articles_comments
                        LEFT JOIN articles
                        ON articles_comments.article_id = articles.article_id
                        LEFT JOIN users
                        ON articles_comments.user_id = users.user_id
                        WHERE comment_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();

                    $res->uri = "{$res->uri}#comment-{$res->item_id}";
                } else if ($res->type == 'article' || $res->type == 'favourite') {
                    // uri, title
                    $st = $this->app->db->prepare("SELECT articles.title, articles.category_id, CONCAT(IF(articles.category_id = 0, '/news/', '/articles/'), articles.slug) AS uri
                        FROM articles
                        WHERE article_id = :item_id
                        LIMIT 1");
                    $st->execute(array(':item_id' => $res->item_id));
                    $st->setFetchMode(PDO::FETCH_INTO, $res);
                    $st->fetch();

                    if ($res->category_id == 0 && $res->type == 'article')
                        $res->type = 'news';
                    unset($res->category_id);
                }

                // Parse title
                if (isset($res->title)) {
                    $res->title = $this->app->parse($res->title, false);
                }

                unset($res->item_id);                
                unset($res->user_id);

                $res->timestamp = $this->app->utils->fdate($res->timestamp);
            }

            return $result;
        }

        public function add($to, $type, $item) {
            $st = $this->app->db->prepare('INSERT INTO users_feed (`user_id`, `type`, `item_id`) VALUES (:to, :type, :item)');
            $result = $st->execute(array(':to' => $to, ':type' => $type, ':item' => $item));
           
            return $result;
        }

        public function remove($id) {
            $st = $this->app->db->prepare("DELETE FROM users_feed
                WHERE user_id = :user_id AND feed_id = :item_id
                LIMIT 1");
            $result = $st->execute(array(':item_id' => $id, ':user_id' => $this->app->user->uid));

            return $result;
        }
    }
?>