<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use Psr\Log\LoggerInterface;

/**
 * Class Instagram
 *
 * Rewrite Code From : https://github.com/mgp25/Instagram-API
 *
 * @package GhoSter\AutoInstagramPost\Model
 */
class Instagram
{
    const API_URL = 'https://i.instagram.com/api/v1/';
    const USER_AGENT = 'Instagram 8.2.0 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US)';
    const IG_SIG_KEY = '55e91155636eaa89ba5ed619eb4645a4daf1103f2161dbfe6fd94d5ea7716095';
    const EXPERIMENTS = 'ig_android_progressive_jpeg,ig_creation_growth_holdout,ig_android_report_and_hide,ig_android_new_browser,ig_android_enable_share_to_whatsapp,ig_android_direct_drawing_in_quick_cam_universe,ig_android_huawei_app_badging,ig_android_universe_video_production,ig_android_asus_app_badging,ig_android_direct_plus_button,ig_android_ads_heatmap_overlay_universe,ig_android_http_stack_experiment_2016,ig_android_infinite_scrolling,ig_fbns_blocked,ig_android_white_out_universe,ig_android_full_people_card_in_user_list,ig_android_post_auto_retry_v7_21,ig_fbns_push,ig_android_feed_pill,ig_android_profile_link_iab,ig_explore_v3_us_holdout,ig_android_histogram_reporter,ig_android_anrwatchdog,ig_android_search_client_matching,ig_android_high_res_upload_2,ig_android_new_browser_pre_kitkat,ig_android_2fac,ig_android_grid_video_icon,ig_android_white_camera_universe,ig_android_disable_chroma_subsampling,ig_android_share_spinner,ig_android_explore_people_feed_icon,ig_explore_v3_android_universe,ig_android_media_favorites,ig_android_nux_holdout,ig_android_search_null_state,ig_android_react_native_notification_setting,ig_android_ads_indicator_change_universe,ig_android_video_loading_behavior,ig_android_black_camera_tab,liger_instagram_android_univ,ig_explore_v3_internal,ig_android_direct_emoji_picker,ig_android_prefetch_explore_delay_time,ig_android_business_insights_qe,ig_android_direct_media_size,ig_android_enable_client_share,ig_android_promoted_posts,ig_android_app_badging_holdout,ig_android_ads_cta_universe,ig_android_mini_inbox_2,ig_android_feed_reshare_button_nux,ig_android_boomerang_feed_attribution,ig_android_fbinvite_qe,ig_fbns_shared,ig_android_direct_full_width_media,ig_android_hscroll_profile_chaining,ig_android_feed_unit_footer,ig_android_media_tighten_space,ig_android_private_follow_request,ig_android_inline_gallery_backoff_hours_universe,ig_android_direct_thread_ui_rewrite,ig_android_rendering_controls,ig_android_ads_full_width_cta_universe,ig_video_max_duration_qe_preuniverse,ig_android_prefetch_explore_expire_time,ig_timestamp_public_test,ig_android_profile,ig_android_dv2_consistent_http_realtime_response,ig_android_enable_share_to_messenger,ig_explore_v3,ig_ranking_following,ig_android_pending_request_search_bar,ig_android_feed_ufi_redesign,ig_android_video_pause_logging_fix,ig_android_default_folder_to_camera,ig_android_video_stitching_7_23,ig_android_profanity_filter,ig_android_business_profile_qe,ig_android_search,ig_android_boomerang_entry,ig_android_inline_gallery_universe,ig_android_ads_overlay_design_universe,ig_android_options_app_invite,ig_android_view_count_decouple_likes_universe,ig_android_periodic_analytics_upload_v2,ig_android_feed_unit_hscroll_auto_advance,ig_peek_profile_photo_universe,ig_android_ads_holdout_universe,ig_android_prefetch_explore,ig_android_direct_bubble_icon,ig_video_use_sve_universe,ig_android_inline_gallery_no_backoff_on_launch_universe,ig_android_image_cache_multi_queue,ig_android_camera_nux,ig_android_immersive_viewer,ig_android_dense_feed_unit_cards,ig_android_sqlite_dev,ig_android_exoplayer,ig_android_add_to_last_post,ig_android_direct_public_threads,ig_android_prefetch_venue_in_composer,ig_android_bigger_share_button,ig_android_dv2_realtime_private_share,ig_android_non_square_first,ig_android_video_interleaved_v2,ig_android_follow_search_bar,ig_android_last_edits,ig_android_video_download_logging,ig_android_ads_loop_count_universe,ig_android_swipeable_filters_blacklist,ig_android_boomerang_layout_white_out_universe,ig_android_ads_carousel_multi_row_universe,ig_android_mentions_invite_v2,ig_android_direct_mention_qe,ig_android_following_follower_social_context';
    const SIG_KEY_VERSION = '4';
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';

    protected $username;            // Instagram username
    protected $password;            // Instagram password
    protected $debug;               // Debug

    protected $uuid;                // UUID
    protected $deviceId;           // Device ID
    protected $usernameId;         // Username ID
    protected $token;               // _csrftoken
    protected $isLoggedIn = false;  // Session status
    protected $rankToken;          // Rank token
    protected $IGDataPath;          // Data storage path
    protected $directoryList;
    protected $config;
    protected $logger;



    /**
     * Default class constructor.
     *
     * @param DirectoryList $directoryList
     * @param InstagramConfig $config
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        DirectoryList $directoryList,
        InstagramConfig $config,
        LoggerInterface $logger,
        array $data = []
    )
    {
        $this->logger = $logger;
        $this->config = $config;

        if ($this->config->isEnabled()) {
            $account = $this->config->getAccountInformation();
            $this->deviceId = $this->generateDeviceId(
                md5($account['username'] . $account['password'])
            );
            $this->setUser($account['username'], $account['password']);
        }

        $this->directoryList = $directoryList;

        $this->IGDataPath = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR;

    }

    /**
     * Set the user. Manage multiple accounts.
     *
     * @param string $username
     *   Your Instagram username.
     * @param string $password
     *   Your Instagram password.
     */
    public function setUser($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->uuid = $this->generateUUID(true);

        if ((file_exists($this->IGDataPath . "$this->username-cookies.dat"))
            && (file_exists($this->IGDataPath . "$this->username-userId.dat"))
            && (file_exists($this->IGDataPath . "$this->username-token.dat"))
        ) {
            $this->isLoggedIn = true;
            $this->usernameId = trim(file_get_contents($this->IGDataPath . "$username-userId.dat"));
            $this->rankToken = $this->usernameId . '_' . $this->uuid;
            $this->token = trim(file_get_contents($this->IGDataPath . "$username-token.dat"));
        }
    }

    /**
     * Login to Instagram.
     *
     * @param bool $force
     *   Force login to Instagram, this will create a new session
     *
     * @return bool
     *    Login data
     * @throws \Exception
     */
    public function login($force = false)
    {
        if (!$this->isLoggedIn || $force) {
            $fetch = $this->request('si/fetch_headers/?challenge_type=signup&guid=' . $this->generateUUID(false), null, true);
            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token);

            $data = [
                'phone_id' => $this->generateUUID(true),
                '_csrftoken' => $token[0],
                'username' => $this->username,
                'guid' => $this->uuid,
                'deviceId' => $this->deviceId,
                'password' => $this->password,
                'login_attempt_count' => '0',
            ];

            $login = $this->request('accounts/login/', $this->generateSignature(json_encode($data)), true);

            if ($login[1]['status'] === 'fail') {
                return false;
            }

            $this->isLoggedIn = true;
            $this->usernameId = $login[1]['logged_in_user']['pk'];
            file_put_contents($this->IGDataPath . $this->username . '-userId.dat', $this->usernameId);
            $this->rankToken = $this->usernameId . '_' . $this->uuid;
            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $login[0], $match);
            $this->token = $match[1];
            file_put_contents($this->IGDataPath . $this->username . '-token.dat', $this->token);

            $this->syncFeatures();
            $this->autoCompleteUserList();
            $this->timelineFeed();

            return true;
        }

        $check = $this->timelineFeed();
        if (isset($check['message'])
            && $check['message'] === 'login_required'
        ) {
            $this->login(true);
        }

        return true;
    }

    public function syncFeatures()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'id' => $this->usernameId,
            '_csrftoken' => $this->token,
            'experiments' => self::EXPERIMENTS,
        ]);

        return $this->request('qe/sync/', $this->generateSignature($data))[1];
    }

    protected function autoCompleteUserList()
    {
        return $this->request('friendships/autocomplete_user_list/')[1];
    }

    protected function timelineFeed()
    {
        return $this->request('feed/timeline/')[1];
    }

    protected function megaphoneLog()
    {
        return $this->request('megaphone/log/')[1];
    }

    protected function expose()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'id' => $this->usernameId,
            '_csrftoken' => $this->token,
            'experiment' => 'ig_android_profile_contextual_feed',
        ]);

        $this->request('qe/expose/', $this->generateSignature($data))[1];
    }

    /**
     * Login to Instagram.
     *
     * @return bool
     *    Returns true if logged out correctly
     */
    public function logout()
    {
        $logout = $this->request('accounts/logout/');

        if ($logout === 'ok') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Upload photo to Instagram.
     *
     * @param string $photo
     *                        Path to your photo
     * @param string $caption
     *                        Caption to be included in your photo.
     *
     * @return array
     *               Upload data
     * @throws \Exception
     */
    public function uploadPhoto($photo, $caption = null, $upload_id = null)
    {
        $endpoint = self::API_URL . 'upload/photo/';
        $boundary = $this->uuid;

        if (!is_null($upload_id)) {
            $fileToUpload = $this->createVideoIcon($photo);
        } else {
            $upload_id = number_format(round(microtime(true) * 1000), 0, '', '');
            $fileToUpload = file_get_contents($photo);
        }

        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'image_compression',
                'data' => '{"lib_name":"jt","lib_version":"1.3.0","quality":"70"}',
            ],
            [
                'type' => 'form-data',
                'name' => 'photo',
                'data' => $fileToUpload,
                'filename' => 'pending_media_' . number_format(round(microtime(true) * 1000), 0, '', '') . '.jpg',
                'headers' => [
                    'Content-Transfer-Encoding: binary',
                    'Content-type: application/octet-stream',
                ],
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Connection: close',
            'Accept: */*',
            'Content-type: multipart/form-data; boundary=' . $boundary,
            'Content-Length: ' . strlen($data),
            'Cookie2: $Version=1',
            'Accept-Language: en-US',
            'Accept-Encoding: gzip',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);

        if ($upload['status'] === 'fail') {
            return [];
        }

        if ($this->debug) {
            $this->logger->critical('RESPONSE: ' . substr($resp, $header_len) . "\n\n");
        }

        $configure = $this->configure($upload['upload_id'], $photo, $caption);
        $this->expose();

        return $configure;
    }

    /**
     * Upload Video
     *
     * @param $video
     * @param null $caption
     * @return mixed|void
     * @throws \Exception
     */
    public function uploadVideo($video, $caption = null)
    {
        $videoData = file_get_contents($video);

        $endpoint = self::API_URL . 'upload/video/';
        $boundary = $this->uuid;
        $upload_id = round(microtime(true) * 1000);
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'media_type',
                'data' => '2',
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->uuid,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Connection: keep-alive',
            'Accept: */*',
            'Host: i.instagram.com',
            'Content-type: multipart/form-data; boundary=' . $boundary,
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $body = json_decode(substr($resp, $header_len), true);

        $uploadUrl = $body['video_upload_urls'][3]['url'];
        $job = $body['video_upload_urls'][3]['job'];

        $request_size = floor(strlen($videoData) / 4);

        $lastRequestExtra = (strlen($videoData) - ($request_size * 4));

        for ($a = 0; $a <= 3; $a++) {
            $start = ($a * $request_size);
            $end = ($a + 1) * $request_size + ($a == 3 ? $lastRequestExtra : 0);

            $headers = [
                'Connection: keep-alive',
                'Accept: */*',
                'Host: upload.instagram.com',
                'Cookie2: $Version=1',
                'Accept-Encoding: gzip, deflate',
                'Content-Type: application/octet-stream',
                'Session-ID: ' . $upload_id,
                'Accept-Language: en-en',
                'Content-Disposition: attachment; filename="video.mov"',
                'Content-Length: ' . ($end - $start),
                'Content-Range: ' . 'bytes ' . $start . '-' . ($end - 1) . '/' . strlen($videoData),
                'job: ' . $job,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $uploadUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($videoData, $start, $end));

            $result = curl_exec($ch);
            $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($result, $header_len);
            $array[] = [$body];
        }
        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);

        if ($upload['status'] === 'fail') {
            throw new \Exception($upload['message']);

            return;
        }

        if ($this->debug) {
            $this->logger->critical('RESPONSE: ' . substr($resp, $header_len) . "\n\n");

        }

        $configure = $this->configureVideo($upload_id, $video, $caption);
        $this->expose();

        return $configure;
    }

    public function direct_share($media_id, $recipients, $text = null)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipeint_users = implode(',', $string);

        $endpoint = self::API_URL . 'direct_v2/threads/broadcast/media_share/?media_type=photo';
        $boundary = $this->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $media_id,
            ],
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipeint_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-type: multipart/form-data; boundary=' . $boundary,
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    protected function configureVideo($upload_id, $video, $caption = '')
    {
        $this->uploadPhoto($video, $caption, $upload_id);

        $size = getimagesize($video)[0];

        $post = json_encode([
            'upload_id' => $upload_id,
            'source_type' => '3',
            'poster_frame_index' => 0,
            'length' => 0.00,
            'audio_muted' => false,
            'filter_type' => '0',
            'video_result' => 'deprecated',
            'clips' => [
                'length' => $this->getSeconds($video),
                'source_type' => '3',
                'camera_position' => 'back',
            ],
            'extra' => [
                'source_width' => 960,
                'source_height' => 1280,
            ],
            'device' => [
                'manufacturer' => 'Xiaomi',
                'model' => 'HM 1SW',
                'android_version' => 18,
                'android_release' => '4.3',
            ],
            '_csrftoken' => $this->token,
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'caption' => $caption,
        ]);

        $post = str_replace('"length":0', '"length":0.00', $post);

        return $this->request('media/configure/?video=1', $this->generateSignature($post))[1];
    }

    protected function configure($upload_id, $photo, $caption = '')
    {
        $size = getimagesize($photo)[0];

        $post = json_encode([
            'upload_id' => $upload_id,
            'camera_model' => 'HM1S',
            'source_type' => 3,
            'date_time_original' => date('Y:m:d H:i:s'),
            'camera_make' => 'XIAOMI',
            'edits' => [
                'crop_original_size' => [$size, $size],
                'crop_zoom' => 1.3333334,
                'crop_center' => [0.0, -0.0],
            ],
            'extra' => [
                'source_width' => $size,
                'source_height' => $size,
            ],
            'device' => [
                'manufacturer' => 'Xiaomi',
                'model' => 'HM 1SW',
                'android_version' => 18,
                'android_release' => '4.3',
            ],
            '_csrftoken' => $this->token,
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'caption' => $caption,
        ]);

        $post = str_replace('"crop_center":[0,0]', '"crop_center":[0.0,-0.0]', $post);

        return $this->request('media/configure/', $this->generateSignature($post))[1];
    }

    /**
     * Edit media.
     *
     * @param string $mediaId
     *   Media id
     * @param string $captionText
     *   Caption text
     *
     * @return array
     *   edit media data
     */
    public function editMedia($mediaId, $captionText = '')
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'caption_text' => $captionText,
        ]);

        return $this->request("media/$mediaId/edit_media/", $this->generateSignature($data))[1];
    }

    /**
     * Remove yourself from a tagged media.
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   edit media data
     */
    public function removeSelftag($mediaId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request("usertags/$mediaId/remove/", $this->generateSignature($data))[1];
    }

    /**
     * Media info
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   delete request data
     */
    public function mediaInfo($mediaId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'media_id' => $mediaId,
        ]);

        return $this->request("media/$mediaId/info/", $this->generateSignature($data))[1];
    }

    /**
     * Delete photo or video.
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   delete request data
     */
    public function deleteMedia($mediaId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'media_id' => $mediaId,
        ]);

        return $this->request("media/$mediaId/delete/", $this->generateSignature($data))[1];
    }

    /**
     * Comment media.
     *
     * @param string $mediaId
     *   Media id
     * @param string $commentText
     *   Comment Text
     *
     * @return array
     *   comment media data
     */
    public function comment($mediaId, $commentText)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'comment_text' => $commentText,
        ]);

        return $this->request("media/$mediaId/comment/", $this->generateSignature($data))[1];
    }

    /**
     * Delete Comment.
     *
     * @param string $mediaId
     *   Media ID
     * @param string $commentId
     *   Comment ID
     *
     * @return array
     *   Delete comment data
     */
    public function deleteComment($mediaId, $captionText, $commentId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'caption_text' => $captionText,
        ]);

        return $this->request("media/$mediaId/comment/$commentId/delete/", $this->generateSignature($data))[1];
    }

    /**
     * Sets account to public.
     *
     * @param string $photo
     *   Path to photo
     */
    public function changeProfilePicture($photo)
    {
        if (is_null($photo)) {
            $this->logger->critical("Photo not valid\n\n");

            return;
        }

        $uData = json_encode([
            '_csrftoken' => $this->token,
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
        ]);

        $endpoint = self::API_URL . 'accounts/change_profile_picture/';
        $boundary = $this->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'ig_sig_key_version',
                'data' => self::SIG_KEY_VERSION,
            ],
            [
                'type' => 'form-data',
                'name' => 'signed_body',
                'data' => hash_hmac('sha256', $uData, self::IG_SIG_KEY) . $uData,
            ],
            [
                'type' => 'form-data',
                'name' => 'profile_pic',
                'data' => file_get_contents($photo),
                'filename' => 'profile_pic',
                'headers' => [
                    'Content-type: application/octet-stream',
                    'Content-Transfer-Encoding: binary',
                ],
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-type: multipart/form-data; boundary=' . $boundary,
            'Accept-Language: en-en',
            'Accept-Encoding: gzip, deflate',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        curl_close($ch);
    }

    /**
     * Remove profile picture.
     *
     * @return array
     *   status request data
     */
    public function removeProfilePicture()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request('accounts/remove_profile_picture/', $this->generateSignature($data))[1];
    }

    /**
     * Sets account to private.
     *
     * @return array
     *   status request data
     */
    public function setPrivateAccount()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request('accounts/set_private/', $this->generateSignature($data))[1];
    }

    /**
     * Sets account to public.
     *
     * @return array
     *   status request data
     */
    public function setPublicAccount()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request('accounts/set_public/', $this->generateSignature($data))[1];
    }

    /**
     * Get personal profile data.
     *
     * @return array
     *   profile data
     */
    public function getProfileData()
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request('accounts/current_user/?edit=true', $this->generateSignature($data))[1];
    }

    /**
     * Edit profile.
     *
     * @param string $url
     *   Url - website. "" for nothing
     * @param string $phone
     *   Phone number. "" for nothing
     * @param string $first_name
     *   Name. "" for nothing
     * @param string $email
     *   Email. Required.
     * @param int $gender
     *   Gender. male = 1 , female = 0
     *
     * @return array
     *   edit profile data
     */
    public function editProfile($url, $phone, $first_name, $biography, $email, $gender)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'external_url' => $url,
            'phone_number' => $phone,
            'username' => $this->username,
            'full_name' => $first_name,
            'biography' => $biography,
            'email' => $email,
            'gender' => $gender,
        ]);

        return $this->request('accounts/edit_profile/', $this->generateSignature($data))[1];
    }

    /**
     * Get username info.
     *
     * @param string $usernameId
     *   Username id
     *
     * @return array
     *   Username data
     */
    public function getUsernameInfo($usernameId)
    {
        return $this->request("users/$usernameId/info/")[1];
    }

    /**
     * Get self username info.
     *
     * @return array
     *   Username data
     */
    public function getSelfUsernameInfo()
    {
        return $this->getUsernameInfo($this->usernameId);
    }

    /**
     * Get recent activity.
     *
     * @return array
     *   Recent activity data
     * @throws \Exception
     */
    public function getRecentActivity()
    {
        $activity = $this->request('news/inbox/?')[1];

        if ($activity['status'] !== 'ok') {
            throw new \Exception($activity['message'] . "\n");

            return;
        }

        return $activity;
    }

    /**
     * Get recent activity from accounts followed.
     *
     * @return array
     *   Recent activity data of follows
     * @throws \Exception
     */
    public function getFollowingRecentActivity()
    {
        $activity = $this->request('news/?')[1];

        if ($activity['status'] !== 'ok') {
            throw new \Exception($activity['message'] . "\n");

            return;
        }

        return $activity;
    }

    /**
     * I dont know this yet.
     *
     * @return array
     *   v2 inbox data
     * @throws \Exception
     */
    public function getv2Inbox()
    {
        $inbox = $this->request('direct_v2/inbox/?')[1];

        if ($inbox['status'] !== 'ok') {
            throw new \Exception($inbox['message'] . "\n");

            return;
        }

        return $inbox;
    }

    /**
     * Get user tags.
     *
     * @param string $usernameId
     *
     * @return array
     *   user tags data
     * @throws \Exception
     */
    public function getUserTags($usernameId)
    {
        $tags = $this->request("usertags/$usernameId/feed/?rankToken=$this->rankToken&ranked_content=true&")[1];

        if ($tags['status'] !== 'ok') {
            throw new \Exception($tags['message'] . "\n");

            return;
        }

        return $tags;
    }

    /**
     * Get self user tags.
     *
     * @return array
     *   self user tags data
     */
    public function getSelfUserTags()
    {
        return $this->getUserTags($this->usernameId);
    }

    /**
     * Get tagged media.
     *
     * @param string $tag
     *
     * @return array
     * @throws \Exception
     */

    public function tagFeed($tag)
    {
        $userFeed = $this->request("feed/tag/$tag/?rankToken=$this->rankToken&ranked_content=true&")[1];

        if ($userFeed['status'] !== 'ok') {
            throw new \Exception($userFeed['message'] . "\n");

            return;
        }

        return $userFeed;
    }

    /**
     * Get media likers.
     *
     * @param string $mediaId
     *
     * @return array
     * @throws \Exception
     */
    public function getMediaLikers($mediaId)
    {
        $likers = $this->request("media/$mediaId/likers/?")[1];
        if ($likers['status'] !== 'ok') {
            throw new \Exception($likers['message'] . "\n");

            return;
        }

        return $likers;
    }

    /**
     * Get user locations media.
     *
     * @param string $usernameId
     *   Username id
     *
     * @return array
     *   Geo Media data
     * @throws \Exception
     */
    public function getGeoMedia($usernameId)
    {
        $locations = $this->request("maps/user/$usernameId/")[1];

        if ($locations['status'] !== 'ok') {
            throw new \Exception($locations['message'] . "\n");

            return;
        }

        return $locations;
    }

    /**
     * Get self user locations media.
     *
     * @return array
     *   Geo Media data
     */
    public function getSelfGeoMedia()
    {
        return $this->getGeoMedia($this->usernameId);
    }

    /**
     * facebook user search.
     *
     * @param string $query
     *
     * @return array
     *   query data
     * @throws \Exception
     */
    public function fbUserSearch($query)
    {
        $query = rawurlencode($query);
        $query = $this->request("fbsearch/topsearch/?context=blended&query=$query&rankToken=$this->rankToken")[1];

        if ($query['status'] !== 'ok') {
            throw new \Exception($query['message'] . "\n");

            return;
        }

        return $query;
    }

    /**
     * Search users.
     *
     * @param string $query
     *
     * @return array
     *   query data
     * @throws \Exception
     */
    public function searchUsers($query)
    {
        $query = $this->request('users/search/?ig_sig_key_version=' . self::SIG_KEY_VERSION . "&is_typeahead=true&query=$query&rankToken=$this->rankToken")[1];

        if ($query['status'] !== 'ok') {
            throw new \Exception($query['message'] . "\n");

            return;
        }

        return $query;
    }

    /**
     * Search exact username
     *
     * @param string usernameName username as STRING not an id
     *
     * @return array
     *   query data
     * @throws \Exception
     *
     */
    public function searchUsername($usernameName)
    {
        $query = $this->request("users/$usernameName/usernameinfo/")[1];

        if ($query['status'] !== 'ok') {
            throw new \Exception($query['message'] . "\n");

            return;
        }

        return $query;
    }

    /**
     * Search users using addres book.
     *
     * @param array $contacts
     *
     * @return array
     *   query data
     */
    public function syncFromAdressBook($contacts)
    {
        $data = 'contacts=' . json_encode($contacts, true);

        return $this->request('address_book/link/?include=extra_display_name,thumbnails', $data)[1];
    }

    /**
     * Search tags.
     *
     * @param string $query
     *
     * @return array
     *   query data
     * @throws \Exception
     */
    public function searchTags($query)
    {
        $query = $this->request("tags/search/?is_typeahead=true&q=$query&rankToken=$this->rankToken")[1];

        if ($query['status'] !== 'ok') {
            throw new \Exception($query['message'] . "\n");

            return;
        }

        return $query;
    }

    /**
     * Get timeline data.
     *
     * @return array
     *   timeline data
     * @throws \Exception
     */
    public function getTimeline($maxid = null)
    {
        $timeline = $this->request(
            "feed/timeline/?rankToken=$this->rankToken&ranked_content=true"
            . (!is_null($maxid) ? "&max_id=" . $maxid : '')
        )[1];

        if ($timeline['status'] !== 'ok') {
            throw new \Exception($timeline['message'] . "\n");

            return;
        }

        return $timeline;
    }

    /**
     * Get user feed.
     * @param string $usernameId
     *    Username id
     * @param null $maxid
     *    Max Id
     * @param null $minTimestamp
     *    Min timestamp
     * @return array User feed data
     *    User feed data
     * @throws \Exception
     */
    public function getUserFeed($usernameId, $maxid = null, $minTimestamp = null)
    {
        $userFeed = $this->request(
            "feed/user/$usernameId/?rankToken=$this->rankToken"
            . (!is_null($maxid) ? "&max_id=" . $maxid : '')
            . (!is_null($minTimestamp) ? "&min_timestamp=" . $minTimestamp : '')
            . "&ranked_content=true"
        )[1];

        if ($userFeed['status'] !== 'ok') {
            throw new \Exception($userFeed['message'] . "\n");

            return;
        }

        return $userFeed;
    }

    /**
     * Get hashtag feed.
     *
     * @param string $hashtagString
     *    Hashtag string, not including the #
     *
     * @return array
     *   Hashtag feed data
     * @throws \Exception
     */
    public function getHashtagFeed($hashtagString, $maxid = null)
    {
        if (is_null($maxid)) {
            $endpoint = "feed/tag/$hashtagString/?rankToken=$this->rankToken&ranked_content=true&";
        } else {
            $endpoint = "feed/tag/$hashtagString/?max_id=" . $maxid . "&rankToken=$this->rankToken&ranked_content=true&";
        }

        $hashtagFeed = $this->request($endpoint)[1];

        if ($hashtagFeed['status'] !== 'ok') {
            throw new \Exception($hashtagFeed['message'] . "\n");

            return;
        }

        return $hashtagFeed;
    }

    /**
     * Get locations.
     *
     * @param string $query
     *    search query
     *
     * @return array
     *   Location location data
     * @throws \Exception
     */
    public function searchLocation($query)
    {
        $query = rawurlencode($query);
        $endpoint = "fbsearch/places/?rankToken=$this->rankToken&query=" . $query;

        $locationFeed = $this->request($endpoint)[1];

        if ($locationFeed['status'] !== 'ok') {
            throw new \Exception($locationFeed['message'] . "\n");

            return;
        }

        return $locationFeed;
    }

    /**
     * Get location feed.
     *
     * @param string $locationId
     *    location id
     *
     * @return array
     *   Location feed data
     * @throws \Exception
     */
    public function getLocationFeed($locationId, $maxid = null)
    {
        if (is_null($maxid)) {
            $endpoint = "feed/location/$locationId/?rankToken=$this->rankToken&ranked_content=true&";
        } else {
            $endpoint = "feed/location/$locationId/?max_id=" . $maxid . "&rankToken=$this->rankToken&ranked_content=true&";
        }

        $locationFeed = $this->request($endpoint)[1];

        if ($locationFeed['status'] !== 'ok') {
            throw new \Exception($locationFeed['message'] . "\n");

            return;
        }

        return $locationFeed;
    }

    /**
     * Get self user feed.
     *
     * @return array
     *   User feed data
     */
    public function getSelfUserFeed()
    {
        return $this->getUserFeed($this->usernameId);
    }

    /**
     * Get popular feed.
     *
     * @return array
     *   popular feed data
     * @throws \Exception
     */
    public function getPopularFeed()
    {
        $popularFeed = $this->request("feed/popular/?people_teaser_supported=1&rankToken=$this->rankToken&ranked_content=true&")[1];

        if ($popularFeed['status'] !== 'ok') {
            throw new \Exception($popularFeed['message'] . "\n");

            return;
        }

        return $popularFeed;
    }

    /**
     * Get user followings.
     *
     * @param string $usernameId
     *   Username id
     *
     * @return array
     *   followers data
     */
    public function getUserFollowings($usernameId, $maxid = null)
    {
        return $this->request("friendships/$usernameId/following/?max_id=$maxid&ig_sig_key_version=" . self::SIG_KEY_VERSION . "&rankToken=$this->rankToken")[1];
    }

    /**
     * Get user followers.
     *
     * @param string $usernameId
     *   Username id
     *
     * @return array
     *   followers data
     */
    public function getUserFollowers($usernameId, $maxid = null)
    {
        return $this->request("friendships/$usernameId/followers/?max_id=$maxid&ig_sig_key_version=" . self::SIG_KEY_VERSION . "&rankToken=$this->rankToken")[1];
    }

    /**
     * Get self user followers.
     *
     * @return array
     *   followers data
     */
    public function getSelfUserFollowers()
    {
        return $this->getUserFollowers($this->usernameId);
    }

    /**
     * Get self users we are following.
     *
     * @return array
     *   users we are following data
     */
    public function getSelfUsersFollowing()
    {
        return $this->request('friendships/following/?ig_sig_key_version=' . self::SIG_KEY_VERSION . "&rankToken=$this->rankToken")[1];
    }

    /**
     * Like photo or video.
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   status request
     */
    public function like($mediaId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'media_id' => $mediaId,
        ]);

        return $this->request("media/$mediaId/like/", $this->generateSignature($data))[1];
    }

    /**
     * Unlike photo or video.
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   status request
     */
    public function unlike($mediaId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            '_csrftoken' => $this->token,
            'media_id' => $mediaId,
        ]);

        return $this->request("media/$mediaId/unlike/", $this->generateSignature($data))[1];
    }

    /**
     * Get media comments.
     *
     * @param string $mediaId
     *   Media id
     *
     * @return array
     *   Media comments data
     */
    public function getMediaComments($mediaId)
    {
        return $this->request("media/$mediaId/comments/?")[1];
    }

    /**
     * Set name and phone (Optional).
     *
     * @param string $name
     * @param string $phone
     *
     * @return array
     *   Set status data
     */
    public function setNameAndPhone($name = '', $phone = '')
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'first_name' => $name,
            'phone_number' => $phone,
            '_csrftoken' => $this->token,
        ]);

        return $this->request('accounts/set_phone_and_name/', $this->generateSignature($data))[1];
    }

    /**
     * Get direct share.
     *
     * @return array
     *   Direct share data
     */
    public function getDirectShare()
    {
        return $this->request('direct_share/inbox/?')[1];
    }

    /**
     * Backups all your uploaded photos :).
     */
    public function backup()
    {
        $myUploads = $this->getSelfUserFeed();
        foreach ($myUploads['items'] as $item) {
            if (!is_dir($this->IGDataPath . 'backup/' . "$this->username-" . date('Y-m-d'))) {
                mkdir($this->IGDataPath . 'backup/' . "$this->username-" . date('Y-m-d'));
            }
            file_put_contents($this->IGDataPath . 'backup/' . "$this->username-" . date('Y-m-d') . '/' . $item['id'] . '.jpg',
                file_get_contents($item['image_versions2']['candidates'][0]['url']));
        }
    }

    /**
     * Follow.
     *
     * @param string $userId
     *
     * @return array
     *   Friendship status data
     */
    public function follow($userId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'user_id' => $userId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request("friendships/create/$userId/", $this->generateSignature($data))[1];
    }

    /**
     * Unfollow.
     *
     * @param string $userId
     *
     * @return array
     *   Friendship status data
     */
    public function unfollow($userId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'user_id' => $userId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request("friendships/destroy/$userId/", $this->generateSignature($data))[1];
    }

    /**
     * Block.
     *
     * @param string $userId
     *
     * @return array
     *   Friendship status data
     */
    public function block($userId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'user_id' => $userId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request("friendships/block/$userId/", $this->generateSignature($data))[1];
    }

    /**
     * Unblock.
     *
     * @param string $userId
     *
     * @return array
     *   Friendship status data
     */
    public function unblock($userId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'user_id' => $userId,
            '_csrftoken' => $this->token,
        ]);

        return $this->request("friendships/unblock/$userId/", $this->generateSignature($data))[1];
    }

    /**
     * Show User Friendship.
     *
     * @param string $userId
     *
     * @return array
     *   Friendship relationship data
     */
    public function userFriendship($userId)
    {
        $data = json_encode([
            '_uuid' => $this->uuid,
            '_uid' => $this->usernameId,
            'user_id' => $userId,
            '_csrftoken' => $this->token,
        ]);
        return $this->request("friendships/show/$userId/", $this->generateSignature($data))[1];
    }

    /**
     * Get liked media.
     *
     * @return array
     *   Liked media data
     */
    public function getLikedMedia()
    {
        return $this->request('feed/liked/?')[1];
    }

    public function generateSignature($data)
    {
        $hash = hash_hmac('sha256', $data, self::IG_SIG_KEY);

        return 'ig_sig_key_version=' . self::SIG_KEY_VERSION . '&signed_body=' . $hash . '.' . urlencode($data);
    }

    public function generateDeviceId($seed)
    {
        // Neutralize username/password -> device correlation
        $volatile_seed = filemtime(__DIR__);

        return 'android-' . substr(md5($seed . $volatile_seed), 16);
    }

    public function generateUUID($type)
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        return $type ? $uuid : str_replace('-', '', $uuid);
    }

    protected function buildBody($bodies, $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: ' . $b['type'] . '; name="' . $b['name'] . '"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="' . 'pending_media_' . number_format(round(microtime(true) * 1000), 0, '', '') . '.' . $ext . '"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n" . $header;
                }
            }

            $body .= "\r\n\r\n" . $b['data'] . "\r\n";
        }
        $body .= '--' . $boundary . '--';

        return $body;
    }

    protected function request($endpoint, $post = null, $login = false)
    {
        if (!$this->isLoggedIn && !$login) {
            throw new \Exception("Not logged in\n");

            return;
        }

        $headers = [
            'Connection: close',
            'Accept: */*',
            'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
            'Cookie2: $Version=1',
            'Accept-Language: en-US',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, self::API_URL . $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);

        curl_close($ch);

        if ($this->debug) {
            $this->logger->critical("REQUEST: $endpoint\n");
            if (!is_null($post)) {
                if (!is_array($post)) {
                    $this->logger->critical('DATA: ' . urldecode($post) . "\n");

                }
            }
            $this->logger->critical("RESPONSE: $body\n\n");
        }

        return [$header, json_decode($body, true)];
    }

    /**
     * Length of the file in Seconds
     *
     * @param string $file
     *    path to the file name
     *
     * @return integer
     *    length of the file in seconds
     */

    function getSeconds($file)
    {
        $ffmpeg = $this->checkFFMPEG();
        if ($ffmpeg) {
            $time = exec("$ffmpeg -i " . $file . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4");
            $duration = explode(':', $time);
            $seconds = $duration[0] * 3600 + $duration[1] * 60 + round($duration[2]);

            return $seconds;
        }

        return mt_rand(15, 300);
    }

    /**
     * Check for ffmpeg/avconv dependencies
     * @return string/boolean
     *    name of the library if present, false otherwise
     */

    function checkFFMPEG()
    {
        @exec('ffmpeg -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'ffmpeg';
        }
        @exec('avconv -version 2>&1', $output, $returnvalue);
        if ($returnvalue === 0) {
            return 'avconv';
        }

        return false;
    }

    /**
     * Creating a video icon/thumbnail
     * @param string $file
     *    path to the video file
     * @return image
     *    icon/thumbnail for the video
     */

    function createVideoIcon($file)
    {
        /* should install ffmpeg for the method to work successfully  */
        $ffmpeg = $this->checkFFMPEG();
        if ($ffmpeg) {
            //generate thumbnail
            $preview = sys_get_temp_dir() . '/' . md5($file) . '.jpg';
            @unlink($preview);

            //capture video preview
            $command = $ffmpeg . ' -i "' . $file . '" -f mjpeg -ss 00:00:01 -vframes 1 "' . $preview . '" 2>&1';
            @exec($command);

            return $this->createIconGD($preview);
        }
    }

    /**
     * Implements the actual logic behind creating the icon/thumbnail
     *
     * @param string $file
     *    path to the file name
     *
     * @return image
     *    icon/thumbnail for the video
     */
    function createIconGD($file, $size = 100, $raw = true)
    {
        list($width, $height) = getimagesize($file);
        if ($width > $height) {
            $y = 0;
            $x = ($width - $height) / 2;
            $smallestSide = $height;
        } else {
            $x = 0;
            $y = ($height - $width) / 2;
            $smallestSide = $width;
        }

        $image_p = imagecreatetruecolor($size, $size);
        $image = imagecreatefromstring(file_get_contents($file));

        imagecopyresampled($image_p, $image, 0, 0, $x, $y, $size, $size, $smallestSide, $smallestSide);
        ob_start();
        imagejpeg($image_p, null, 95);
        $i = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);
        imagedestroy($image_p);

        return $i;
    }
}