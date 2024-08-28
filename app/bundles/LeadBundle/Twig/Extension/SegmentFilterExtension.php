<?php

declare(strict_types=1);

namespace Mautic\LeadBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SegmentFilterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getSegmentFilterIcon', [$this, 'getSegmentFilterIcon']),
        ];
    }

    public function getSegmentFilterIcon(string $filterType): string
    {
        return match ($filterType) {
            // lead
            'address1'               => 'ri-home-2-line',
            'address2'               => 'ri-home-3-line',
            'attribution'            => 'ri-cash-line',
            'attribution_date'       => 'ri-calendar-event-line',
            'dnc_bounced'            => 'ri-mail-close-line',
            'dnc_bounced_sms'        => 'ri-chat-delete-line',
            'campaign'               => 'ri-megaphone-line',
            'city'                   => 'ri-building-2-line',
            'country'                => 'ri-earth-line',
            'date_added'             => 'ri-calendar-check-line',
            'date_identified'        => 'ri-calendar-todo-line',
            'last_active'            => 'ri-time-line',
            'device_brand'           => 'ri-smartphone-line',
            'device_model'           => 'ri-device-line',
            'device_os'              => 'ri-window-2-line',
            'device_type'            => 'ri-computer-line',
            'email'                  => 'ri-mail-line',
            'generated_email_domain' => 'ri-at-line',
            'facebook'               => 'ri-facebook-box-line',
            'fax'                    => 'ri-printer-line',
            'firstname'              => 'ri-user-line',
            'foursquare'             => 'ri-map-pin-user-line',
            'instagram'              => 'ri-instagram-line',
            'lastname'               => 'ri-user-2-line',
            'mobile'                 => 'ri-smartphone-line',
            'date_modified'          => 'ri-calendar-event-line',
            'owner_id'               => 'ri-user-star-line',
            'phone'                  => 'ri-phone-line',
            'points'                 => 'ri-coins-line',
            'position'               => 'ri-briefcase-4-line',
            'preferred_locale'       => 'ri-translate-2',
            'timezone'               => 'ri-time-zone-line',
            'company'                => 'ri-building-4-line',
            'leadlist'               => 'ri-list-check-2',
            'skype'                  => 'ri-skype-line',
            'stage'                  => 'ri-barricade-line',
            'state'                  => 'ri-map-pin-2-line',
            'globalcategory'         => 'ri-folder-2-line',
            'tags'                   => 'ri-hashtag',
            'title'                  => 'ri-user-star-line',
            'twitter'                => 'ri-twitter-x-line',
            'utm_campaign'           => 'ri-bookmark-2-line',
            'utm_content'            => 'ri-file-text-line',
            'utm_medium'             => 'ri-share-line',
            'utm_source'             => 'ri-link',
            'utm_term'               => 'ri-hashtag',
            'dnc_unsubscribed'       => 'ri-forbid-2-line',
            'dnc_unsubscribed_sms'   => 'ri-forbid-2-line',
            'dnc_manual_email'       => 'ri-mail-forbid-line',
            'dnc_manual_sms'         => 'ri-chat-off-line',
            'website'                => 'ri-global-line',
            'zipcode'                => 'ri-mail-send-line',
            'linkedin'               => 'ri-linkedin-box-line',

            // company
            'companyaddress1'            => 'ri-building-line',
            'companyaddress2'            => 'ri-building-2-line',
            'companyannual_revenue'      => 'ri-money-dollar-circle-line',
            'companycity'                => 'ri-building-4-line',
            'companyemail'               => 'ri-mail-line',
            'companyname'                => 'ri-building-3-line',
            'companycountry'             => 'ri-global-line',
            'companydescription'         => 'ri-file-text-line',
            'companyfax'                 => 'ri-printer-line',
            'companyindustry'            => 'ri-briefcase-4-line',
            'companynumber_of_employees' => 'ri-team-line',
            'companyphone'               => 'ri-phone-line',
            'companystate'               => 'ri-map-pin-2-line',
            'companywebsite'             => 'ri-global-line',
            'companyzipcode'             => 'ri-mail-send-line',

            // behaviors
            'redirect_id'             => 'ri-cursor-line',
            'email_id'                => 'ri-cursor-line',
            'email_clicked_link_date' => 'ri-cursor-line',
            'sms_clicked_link'        => 'ri-cursor-line',
            'sms_clicked_link_date'   => 'ri-cursor-line',
            'lead_asset_download'     => 'ri-download-2-line',
            'sessions'                => 'ri-timer-line',
            'notification'            => 'ri-notification-3-line',
            'lead_email_received'     => 'ri-mail-open-line ',
            'lead_email_read_date'    => 'ri-mail-open-line',
            'lead_email_read_count'   => 'ri-mail-open-line',
            'lead_email_sent_date'    => 'ri-send-plane-2-line',
            'hit_url'                 => 'ri-external-link-line',
            'page_id'                 => 'ri-external-link-line',
            'hit_url_date'            => 'ri-external-link-line',
            'hit_url_count'           => 'ri-external-link-line',
            'referer'                 => 'ri-external-link-line',
            'source'                  => 'ri-external-link-line',
            'source_id'               => 'ri-external-link-line',
            'url_title'               => 'ri-external-link-line',
            'lead_email_sent'         => 'ri-mail-send-line',
            default                   => 'ri-shapes-line',
        };
    }
}
