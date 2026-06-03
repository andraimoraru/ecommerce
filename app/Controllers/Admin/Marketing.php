<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\MarketingSettings;

final class Marketing extends Controller
{
    private const CHANNELS = ['INSTAGRAM', 'FACEBOOK'];

    // Store a one-time admin flash message for the next page load.
    private function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    // Render the main marketing admin page.
    public function index(): void
    {
        $this->renderSocialPage('Marketing');
    }

    // Render the social settings form for Instagram and Facebook.
    public function social(): void
    {
        $this->renderSocialPage('Social Marketing');
    }

    // Validate and persist social marketing settings.
    public function saveSocial(): void
    {
        $old = $this->collectInput();
        $errors = $this->validateInput($old);
        $settingsModel = new MarketingSettings();
        $existingSettings = $settingsModel->allByChannel();

        if ($errors) {
            $this->render('admin/marketing/social', [
                'title' => 'Social Marketing',
                'channels' => self::CHANNELS,
                'settings' => $this->mergeSettings($existingSettings, $old),
                'errors' => $errors,
            ], 'admin');
            return;
        }

        foreach (self::CHANNELS as $channel) {
            $settingsModel->saveForChannel($channel, $old[$channel]);
        }

        $this->setFlash('success', 'Marketing settings saved successfully.');

        header('Location: ' . URLROOT . '/admin/marketing/social');
        exit;
    }

    // Render the shared social settings view.
    private function renderSocialPage(string $title): void
    {
        $settings = (new MarketingSettings())->allByChannel();

        $this->render('admin/marketing/social', [
            'title' => $title,
            'channels' => self::CHANNELS,
            'settings' => $this->mergeSettings($settings),
            'errors' => [],
        ], 'admin');
    }

    /**
     * @return array<string,array<string,string>>
     */
    // Collect expected posted fields for both supported channels.
    private function collectInput(): array
    {
        $posted = $_POST['settings'] ?? [];
        $data = [];

        foreach (self::CHANNELS as $channel) {
            $row = is_array($posted) && isset($posted[$channel]) && is_array($posted[$channel])
                ? $posted[$channel]
                : [];

            $data[$channel] = [
                'channel' => $channel,
                'profile_url' => trim((string)($row['profile_url'] ?? '')),
                'username' => trim((string)($row['username'] ?? '')),
                'page_id' => trim((string)($row['page_id'] ?? '')),
                'access_token' => trim((string)($row['access_token'] ?? '')),
            ];
        }

        return $data;
    }

    /**
     * @param array<string,array<string,string>> $data
     * @return array<string,array<string,string>>
     */
    // Apply the simple channel and field validation rules.
    private function validateInput(array $data): array
    {
        $errors = [];

        foreach ($data as $channel => $row) {
            if (!in_array($channel, self::CHANNELS, true) || ($row['channel'] ?? '') !== $channel) {
                $errors[$channel]['channel'] = 'Channel must be Instagram or Facebook.';
            }

            if (($row['profile_url'] ?? '') !== '' && !filter_var($row['profile_url'], FILTER_VALIDATE_URL)) {
                $errors[$channel]['profile_url'] = 'Enter a valid URL.';
            }

            if (mb_strlen((string)($row['username'] ?? '')) > 120) {
                $errors[$channel]['username'] = 'Username must be 120 characters or fewer.';
            }

            if (mb_strlen((string)($row['page_id'] ?? '')) > 120) {
                $errors[$channel]['page_id'] = 'Page ID must be 120 characters or fewer.';
            }
        }

        return $errors;
    }

    /**
     * @param array<string,array<string,mixed>> $settings
     * @param array<string,array<string,string>> $old
     * @return array<string,array<string,mixed>>
     */
    // Normalise saved settings so the view can render both channels consistently.
    private function mergeSettings(array $settings, array $old = []): array
    {
        $merged = [];

        foreach (self::CHANNELS as $channel) {
            $row = $old[$channel] ?? $settings[$channel] ?? [];
            $savedToken = (string)($settings[$channel]['access_token'] ?? '');

            $merged[$channel] = [
                'channel' => $channel,
                'profile_url' => (string)($row['profile_url'] ?? ''),
                'username' => (string)($row['username'] ?? ''),
                'page_id' => (string)($row['page_id'] ?? ''),
                'access_token_status' => $savedToken !== '' ? 'saved' : 'not saved',
                'updated_at' => $settings[$channel]['updated_at'] ?? null,
            ];
        }

        return $merged;
    }
}
