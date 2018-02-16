<?php
namespace com\selfcoders\statuspage;

use com\selfcoders\phpcurl\Curl;
use com\selfcoders\statuspage\objects\Component;

class Connection
{
    /**
     * @var string
     */
    private $apiKey;
    /**
     * @var string
     */
    private $pageId;
    /**
     * @var string
     */
    private $userAgent;
    /**
     * @var String
     */
    private $proxy;

    public function __construct(string $apiKey, string $pageId, String $userAgent = null, String $proxy = null)
    {
        if ($userAgent === null) {
            $curlVersion = curl_version();

            $details = array
            (
                php_uname("s"),
                php_uname("r"),
                "PHP/" . PHP_VERSION,
                "curl/" . $curlVersion["version"]
            );

            $userAgent = sprintf("PHP Statuspage.io Client (%s)", implode("; ", $details));
        }

        $this->apiKey = $apiKey;
        $this->pageId = $pageId;
        $this->userAgent = $userAgent;
        $this->proxy = $proxy;
    }

    /**
     * @return Component[]
     * @throws ApiException
     */
    public function getComponents()
    {
        $list = $this->get(sprintf("/pages/%s/components.json", $this->pageId));

        $components = array();

        foreach ($list as $entry) {
            $components[] = Component::createFromArray($entry);
        }

        return $components;
    }

    /**
     * @param Component $component
     * @return Connection
     * @throws ApiException
     */
    public function createComponent(Component $component)
    {
        /**
         * component[name] - The name of the component
         * component[description] - The description of the component
         * component[group_id] - The id of the parent component (optional, only 1 level of nesting)
         */

        $data = array
        (
            "component" => array
            (
                "name" => $component->name,
                "description" => $component->description,
                "group_id" => $component->groupId
            )
        );

        $component->setFromArray($this->post(sprintf("/pages/%s/components.json", $this->pageId), $data));

        return $this;
    }

    /**
     * @param Component $component
     * @return Connection
     * @throws ApiException
     */
    public function updateComponent(Component $component)
    {
        /**
         * component[name] - The name of the component
         * component[description] - The description of the component
         * component[status] - The status, one of operational|degraded_performance|partial_outage|major_outage|under_maintenance. To set a 3rd party component back to its official status, pass in a blank string ("component[status]=")
         */

        $data = array
        (
            "component" => array
            (
                "name" => $component->name,
                "description" => $component->description,
                "status" => $component->status
            )
        );

        $component->setFromArray($this->patch(sprintf("/pages/%s/components/%s.json", $this->pageId, $component->id), $data));

        return $this;
    }

    /**
     * @param Component $component
     * @throws ApiException
     */
    public function deleteComponent(Component $component)
    {
        $this->delete(sprintf("/pages/%s/components/%s.json", $this->pageId, $component->id));
    }

    /**
     * @param string $url
     * @param string $method
     * @param mixed|null $postData
     * @return mixed
     * @throws ApiException
     */
    private function request(string $url, string $method = "GET", $postData = null)
    {
        $curl = new Curl(sprintf("https://api.statuspage.io/v1/%s", ltrim($url, "/")));

        $curl->setOpt(CURLOPT_USERAGENT, $this->userAgent);
        $curl->setOpt(CURLOPT_PROXY, $this->proxy);
        $curl->setOpt(CURLOPT_CUSTOMREQUEST, $method);

        if ($postData !== null) {
            $curl->setOpt(CURLOPT_HTTPHEADER, array
            (
                "Content-Type: application/json"
            ));

            $curl->setOpt(CURLOPT_POSTFIELDS, json_encode($postData));
        }

        $response = $curl->exec();

        if (!$curl->isSuccessful()) {
            $httpStatus = $curl->getInfo(CURLINFO_HTTP_CODE);
            $time = $curl->getInfo(CURLINFO_TOTAL_TIME);

            throw new ApiException(sprintf("Invalid response from Statuspage API (HTTP status %d, took %d seconds): %s", $httpStatus, $time, $response), $httpStatus);
        }

        return json_decode($response, true);
    }

    /**
     * @param string $url
     * @return mixed
     * @throws ApiException
     */
    private function get(string $url)
    {
        return $this->request($url);
    }

    /**
     * @param string $url
     * @param mixed $data
     * @return mixed
     * @throws ApiException
     */
    private function post(string $url, $data)
    {
        return $this->request($url, "POST", $data);
    }

    /**
     * @param string $url
     * @param $data
     * @return mixed
     * @throws ApiException
     */
    private function patch(string $url, $data)
    {
        return $this->request($url, "PATCH", $data);
    }

    /**
     * @param string $url
     * @param $data
     * @return mixed
     * @throws ApiException
     */
    private function put(string $url, $data)
    {
        return $this->request($url, "PUT", $data);
    }

    /**
     * @param string $url
     * @throws ApiException
     */
    private function delete(string $url)
    {
        $this->request($url, "DELETE");
    }
}