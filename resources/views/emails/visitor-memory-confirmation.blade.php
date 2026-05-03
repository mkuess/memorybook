<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bitte bestätige deine Erinnerung</title>
</head>
<body style="font-family: sans-serif; background: #f8f5ed; padding: 32px 16px; color: #2f2e2a;">
    <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 40px 32px; border: 1px solid #ddd6ca;">

        <h2 style="margin-top: 0; font-size: 20px; color: #2f2e2a;">Deine Erinnerung bestätigen</h2>

        <p style="color: #706b62; line-height: 1.6;">
            Vielen Dank, dass du eine Erinnerung hinterlassen möchtest.<br>
            Bitte klicke auf den folgenden Link, um deine Erinnerung zu bestätigen und zu veröffentlichen.
        </p>

        <p style="margin: 28px 0;">
            <a href="{{ route('visitor-memory.confirm', ['code' => $shortCode, 'token' => $story->visitor_token]) }}"
               style="display: inline-block; padding: 12px 24px; background: #8fa7b0; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 14px;">
                Erinnerung bestätigen
            </a>
        </p>

        <p style="color: #706b62; font-size: 13px; line-height: 1.6;">
            Dieser Link ist 7 Tage gültig. Falls du keine Erinnerung hinterlassen hast, kannst du diese E-Mail ignorieren.
        </p>

        <hr style="border: none; border-top: 1px solid #ddd6ca; margin: 24px 0;">

        <p style="color: #9b9490; font-size: 12px; margin: 0;">
            Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:<br>
            <span style="word-break: break-all; color: #5f737b;">
                {{ route('visitor-memory.confirm', ['code' => $shortCode, 'token' => $story->visitor_token]) }}
            </span>
        </p>

    </div>
</body>
</html>
