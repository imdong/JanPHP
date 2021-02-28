<?php


namespace Framework;

/**
 * 基类
 *
 * @package Framework
 */
class Controller
{
    /**
     * @var array 响应数据
     */
    protected $response = [
        'content_type' => 'text/html',
        'body'         => null
    ];

    /**
     * 响应 JSON 数据
     *
     * @param $data
     * @return $this
     */
    public function json($data): self
    {
        $this->response = [
            'content_type' => 'application/json',
            'body'         => json_encode($data)
        ];

        return $this;
    }

    /**
     * 发送响应
     *
     * @param null $data
     * @return void
     */
    public function sendResponse($data = null)
    {
        // 尝试解析出 data 的响应方式
        if (!is_null($data)) {
            if (is_array($data)) {
                $this->response = [
                    'content_type' => 'application/json',
                    'body'         => json_encode($data)
                ];
            } else {
                $this->response['body'] = $data;
            }
        }

        // 输出响应头
        header(sprintf('Content-Type: %s', $this->response['content_type']));
        echo $this->response['body'];
    }
}
