<?php

namespace GhoSter\AutoInstagramPost\Model;

use Exception;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

/**
 * Class Instagram API to communicate with Instagram
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPCS.Magento2.Files.LineLength.MaxExceeded)
 */
class Instagram
{
    const API_DOMAIN = 'https://i.instagram.com/';
    const API_URL = 'https://i.instagram.com/api/v1/';
    const USER_AGENT = 'Instagram 8.2.0 Android (18/4.3; 320dpi; 720x1280; Xiaomi; HM 1SW; armani; qcom; en_US)';
    const IG_SIG_KEY = '55e91155636eaa89ba5ed619eb4645a4daf1103f2161dbfe6fd94d5ea7716095';
    /**
     * phpcs:disable Generic.Files.LineLength.TooLong
     */
    const EXPERIMENTS = 'ig_android_progressive_jpeg,ig_creation_growth_holdout,ig_android_report_and_hide,ig_android_new_browser,ig_android_enable_share_to_whatsapp,ig_android_direct_drawing_in_quick_cam_universe,ig_android_huawei_app_badging,ig_android_universe_video_production,ig_android_asus_app_badging,ig_android_direct_plus_button,ig_android_ads_heatmap_overlay_universe,ig_android_http_stack_experiment_2016,ig_android_infinite_scrolling,ig_fbns_blocked,ig_android_white_out_universe,ig_android_full_people_card_in_user_list,ig_android_post_auto_retry_v7_21,ig_fbns_push,ig_android_feed_pill,ig_android_profile_link_iab,ig_explore_v3_us_holdout,ig_android_histogram_reporter,ig_android_anrwatchdog,ig_android_search_client_matching,ig_android_high_res_upload_2,ig_android_new_browser_pre_kitkat,ig_android_2fac,ig_android_grid_video_icon,ig_android_white_camera_universe,ig_android_disable_chroma_subsampling,ig_android_share_spinner,ig_android_explore_people_feed_icon,ig_explore_v3_android_universe,ig_android_media_favorites,ig_android_nux_holdout,ig_android_search_null_state,ig_android_react_native_notification_setting,ig_android_ads_indicator_change_universe,ig_android_video_loading_behavior,ig_android_black_camera_tab,liger_instagram_android_univ,ig_explore_v3_internal,ig_android_direct_emoji_picker,ig_android_prefetch_explore_delay_time,ig_android_business_insights_qe,ig_android_direct_media_size,ig_android_enable_client_share,ig_android_promoted_posts,ig_android_app_badging_holdout,ig_android_ads_cta_universe,ig_android_mini_inbox_2,ig_android_feed_reshare_button_nux,ig_android_boomerang_feed_attribution,ig_android_fbinvite_qe,ig_fbns_shared,ig_android_direct_full_width_media,ig_android_hscroll_profile_chaining,ig_android_feed_unit_footer,ig_android_media_tighten_space,ig_android_private_follow_request,ig_android_inline_gallery_backoff_hours_universe,ig_android_direct_thread_ui_rewrite,ig_android_rendering_controls,ig_android_ads_full_width_cta_universe,ig_video_max_duration_qe_preuniverse,ig_android_prefetch_explore_expire_time,ig_timestamp_public_test,ig_android_profile,ig_android_dv2_consistent_http_realtime_response,ig_android_enable_share_to_messenger,ig_explore_v3,ig_ranking_following,ig_android_pending_request_search_bar,ig_android_feed_ufi_redesign,ig_android_video_pause_logging_fix,ig_android_default_folder_to_camera,ig_android_video_stitching_7_23,ig_android_profanity_filter,ig_android_business_profile_qe,ig_android_search,ig_android_boomerang_entry,ig_android_inline_gallery_universe,ig_android_ads_overlay_design_universe,ig_android_options_app_invite,ig_android_view_count_decouple_likes_universe,ig_android_periodic_analytics_upload_v2,ig_android_feed_unit_hscroll_auto_advance,ig_peek_profile_photo_universe,ig_android_ads_holdout_universe,ig_android_prefetch_explore,ig_android_direct_bubble_icon,ig_video_use_sve_universe,ig_android_inline_gallery_no_backoff_on_launch_universe,ig_android_image_cache_multi_queue,ig_android_camera_nux,ig_android_immersive_viewer,ig_android_dense_feed_unit_cards,ig_android_sqlite_dev,ig_android_exoplayer,ig_android_add_to_last_post,ig_android_direct_public_threads,ig_android_prefetch_venue_in_composer,ig_android_bigger_share_button,ig_android_dv2_realtime_private_share,ig_android_non_square_first,ig_android_video_interleaved_v2,ig_android_follow_search_bar,ig_android_last_edits,ig_android_video_download_logging,ig_android_ads_loop_count_universe,ig_android_swipeable_filters_blacklist,ig_android_boomerang_layout_white_out_universe,ig_android_ads_carousel_multi_row_universe,ig_android_mentions_invite_v2,ig_android_direct_mention_qe,ig_android_following_follower_social_context';
    const SIG_KEY_VERSION = '4';
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $deviceId;

    /**
     * @var string
     */
    protected $usernameId;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var bool
     */
    protected $isLoggedIn = false;

    /**
     * @var string
     */
    protected $rankToken;

    /**
     * @var string
     */
    protected $IGDataPath;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Default class constructor.
     *
     * @param DirectoryList $directoryList
     * @param InstagramConfig $config
     * @param LoggerInterface $logger
     * @param array $data
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        InstagramConfig $config,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->config = $config;

        if ($this->config->isEnabled()) {
            $this->deviceId = $this->generateDeviceId();
        }

        $this->directoryList = $directoryList;

        $this->debug = $this->config->isDebugEnabled();
        $this->IGDataPath = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR;
    }

    /**
     * @return string
     */
    public function generateDeviceId()
    {
        // phpcs:disable Magento2.Security.InsecureFunction
        $megaRandomHash = md5(number_format(microtime(true), 7, '', ''));
        return 'android-' . substr($megaRandomHash, 16);
    }

    /**
     * Set the user. Manage multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     */
    public function setUser($username, $password)
    {
        $this->username = $username;
        $this->password = $password;

        $this->uuid = $this->generateUUID(true);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if ((file_exists($this->IGDataPath . "$this->username-cookies.dat"))
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            && (file_exists($this->IGDataPath . "$this->username-userId.dat"))
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            && (file_exists($this->IGDataPath . "$this->username-token.dat"))
        ) {
            $this->isLoggedIn = true;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->usernameId = trim(file_get_contents($this->IGDataPath . "$username-userId.dat"));
            $this->rankToken = $this->usernameId . '_' . $this->uuid;
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->token = trim(file_get_contents($this->IGDataPath . "$username-token.dat"));
        }
    }

    /**
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     *
     * @param bool $keepDashes
     * @return string|string[]
     */
    public function generateUUID($keepDashes = true)
    {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );

        return $keepDashes ? $uuid : str_replace('-', '', $uuid);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        $account = $this->config->getAccountInformation();
        $username = isset($account['username']) ? ($account['username']) : null;

        if (!empty($username)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if ((file_exists($this->IGDataPath . "$username-cookies.dat"))
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                && (file_exists($this->IGDataPath . "$username-userId.dat"))
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                && (file_exists($this->IGDataPath . "$username-token.dat"))
            ) {
                $this->isLoggedIn = true;
            }
        }

        return $this->isLoggedIn;
    }

    /**
     * Login to Instagram.
     *
     * @param bool $force Force login to Instagram, this will create a new session
     *
     * @return bool
     * @throws Exception
     */
    public function login($force = false)
    {
        if (!$this->isLoggedIn || $force) {
            $fetch = $this->request(
                'si/fetch_headers/?challenge_type=signup&guid=' . $this->generateUUID(false),
                null,
                true
            );

            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token);

            $data = [
                'phone_id' => $this->generateUUID(true),
                '_csrftoken' => $token[0],
                'username' => $this->username,
                'guid' => $this->uuid,
                'device_id' => $this->deviceId,
                'password' => $this->password,
                'login_attempt_count' => '0',
            ];

            $login = $this->request('accounts/login/', $this->generateSignature(json_encode($data)), true);

            if ($login[1]['status'] === self::STATUS_FAIL) {
                return false;
            }

            $this->isLoggedIn = true;
            $this->usernameId = $login[1]['logged_in_user']['pk'];
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            file_put_contents($this->IGDataPath . $this->username . '-userId.dat', $this->usernameId);
            $this->rankToken = $this->usernameId . '_' . $this->uuid;
            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $login[0], $match);
            $this->token = $match[1];
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
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

    /**
     *
     * phpcs:disable Magento2.Functions.DiscouragedFunction
     *
     * @param $endpoint
     * @param null $post
     * @param bool $login
     * @return array
     * @throws Exception
     */
    protected function request($endpoint, $post = null, $login = false)
    {
        if (!$this->isLoggedIn && !$login) {
            //phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new Exception("Not logged in");
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
            if (!empty($post)) {
                if (!is_array($post)) {
                    $this->logger->critical('DATA: ' . urldecode($post) . "\n");
                }
            }
            $this->logger->critical("RESPONSE: $body\n\n");
        }

        return [$header, json_decode($body, true)];
    }

    /**
     * @param $data
     * @return string
     */
    public function generateSignature($data)
    {
        $hash = hash_hmac('sha256', $data, self::IG_SIG_KEY);

        return 'ig_sig_key_version=' . self::SIG_KEY_VERSION . '&signed_body=' . $hash . '.' . urlencode($data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
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

    /**
     * @return mixed
     * @throws Exception
     */
    protected function autoCompleteUserList()
    {
        return $this->request('friendships/autocomplete_user_list/')[1];
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function timelineFeed()
    {
        return $this->request('feed/timeline/')[1];
    }

    /**
     * Login to Instagram.
     *
     * @return bool
     * @throws Exception
     */
    public function logout()
    {
        $logout = $this->request('accounts/logout/');
        return $logout === self::STATUS_OK;
    }

    /**
     * Upload photo to Instagram.
     *
     * @param string $photo
     * @param string $caption
     * @param null $uploadId
     * @return array
     * @throws Exception
     */
    public function uploadPhoto($photo, $caption = null, $uploadId = null)
    {
        $endpoint = self::API_DOMAIN . 'rupload_igphoto/';

        if (!empty($uploadId)) {
            $fileToUpload = $this->createVideoIcon($photo);
        } else {
            $uploadId = number_format(round(microtime(true) * 1000), 0, '', '');
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $fileToUpload = file_get_contents($photo, FILE_BINARY);
        }

        $uploadName = "fb_uploader_" . $uploadId;
        $endpoint = $endpoint . $uploadName;

        $uploadParams = [
            'retry_context' => '{"num_step_auto_retry":0,"num_reupload":0,"num_step_manual_retry":0}',
            'media_type' => '1',
            'xsharing_user_ids' => '[]',
            'upload_id' => $uploadId,
            'image_compression' => json_encode(
                ['lib_name' => 'moz', 'lib_version' => '3.1.m', 'quality' => '80']
            )
        ];

        $headers = [
            'Accept-Encoding: gzip',
            'X-Instagram-Rupload-Params: ' . json_encode($uploadParams),
            'X_FB_PHOTO_WATERFALL_ID: ' . $this->generateUUID(true),
            'X-Entity-Type: image/jpeg',
            'Offset: 0',
            'X-Entity-Name: ' . $uploadName,
            'X-Entity-Length: ' . strlen($fileToUpload),
            'Content-Type: application/octet-stream',
        ];

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $ch = curl_init();
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_HEADER, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . "$this->username-cookies.dat");
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . "$this->username-cookies.dat");
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_POST, true);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileToUpload);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $resp = curl_exec($ch);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $headerLength = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $upload = json_decode(substr($resp, $headerLength), true);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        curl_close($ch);

        if ($upload['status'] === self::STATUS_FAIL) {
            return [];
        }

        if ($this->debug) {
            $this->logger->critical('RESPONSE: ' . substr($resp, $headerLength) . "\n\n");
        }

        $configure = $this->configure($upload['upload_id'], $photo, $caption);
        $this->expose();

        return $configure;
    }

    /**
     * Creating a video icon/thumbnail
     * phpcs:disable Generic.PHP.NoSilencedErrors
     * phpcs:disable Magento2.Security.InsecureFunction
     *
     * @param string $file path to the video file
     * @return false|string
     */
    public function createVideoIcon($file)
    {
        $ffmpeg = $this->checkFFMPEG();
        if ($ffmpeg) {
            //phpcs:ignore Magento2.Security.InsecureFunction
            $preview = sys_get_temp_dir() . '/' . md5($file) . '.jpg';
            // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
            @unlink($preview);

            $command = $ffmpeg . ' -i "' . $file . '" -f mjpeg -ss 00:00:01 -vframes 1 "' . $preview . '" 2>&1';
            @exec($command);

            return $this->createIconGD($preview);
        }

        return false;
    }

    /**
     * Check for ffmpeg/avconv dependencies
     *
     * phpcs:disable Generic.PHP.NoSilencedErrors
     * phpcs:disable Magento2.Security.InsecureFunction
     *
     * @return string|boolean name of the library if present, false otherwise
     */
    public function checkFFMPEG()
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
     * Implements the actual logic behind creating the icon/thumbnail
     *
     * @param string $file path to the file name
     *
     * @param int $size
     * @return string|false icon/thumbnail for the video
     */
    public function createIconGD($file, $size = 100)
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

    /**
     * @param $upload_id
     * @param $photo
     * @param string $caption
     * @return mixed
     * @throws Exception
     */
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
     * @throws Exception
     */
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
}
