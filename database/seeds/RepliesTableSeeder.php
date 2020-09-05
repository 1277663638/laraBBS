<?php

use Illuminate\Database\Seeder;
use App\Models\Reply;
use App\Models\User;
use App\Models\Topic;

class RepliesTableSeeder extends Seeder
{
    public function run()
    {
        // 所有用户 ID 数组，如：[1,2,3,4]
        $user_ids = User::all()->pluck('id')->toArray();

        // 所有话题 ID 数组，如：[1,2,3,4]
        $topic_ids = Topic::all()->pluck('id')->toArray();

        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);

        $replies = factory(Reply::class)
                        ->times(1000)
                        ->make()
                        ->each(function ($reply, $index)
                            use ($user_ids, $topic_ids, $faker)
        {
            // 从用户 ID 数组中随机取出一个并赋值
            $reply->user_id = $faker->randomElement($user_ids);

            // 话题 ID，同上
            $reply->topic_id = $faker->randomElement($topic_ids);
        });

        // 将数据集合转换为数组，并插入到数据库中
        Reply::insert($replies->toArray());


        \DB::update("UPDATE topics AS t SET last_reply_user_id = IFNULL((
            SELECT
                user_id
            FROM
                replies AS r
            WHERE
                r.created_at = (
                    SELECT
                        max(created_at)
                    FROM
                        replies AS b
                    WHERE
                        r.topic_id = b.topic_id
                    AND t.id = b.topic_id
                )
        ),0)");
        \DB::update('UPDATE topics AS t
        SET reply_count = IFNULL((
            SELECT
                count(*)
            FROM
                replies AS r
            WHERE
                t.id = r.topic_id
        ),0)');

    }

}

