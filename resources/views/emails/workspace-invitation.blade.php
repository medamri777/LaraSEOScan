<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace Invitation</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f4f6f8; color: #1a1a2e; }
        .wrapper { max-width: 560px; margin: 40px auto; }
        .card { background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .header { background: {{ $invitation->tenant->primary_color ?? '#3B82F6' }}; padding: 28px 32px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.8); font-size: 13px; margin-top: 4px; }
        .body { padding: 32px; }
        .body p { font-size: 15px; line-height: 1.6; color: #374151; margin-bottom: 16px; }
        .workspace-box { background: #f0f4ff; border: 1px solid #dbeafe; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .workspace-box .ws-name { font-size: 18px; font-weight: 700; color: {{ $invitation->tenant->primary_color ?? '#3B82F6' }}; }
        .workspace-box .ws-meta { font-size: 13px; color: #6b7280; margin-top: 4px; }
        .badge { display: inline-block; background: #dbeafe; color: #1e40af; font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 9999px; text-transform: capitalize; }
        .btn-wrap { text-align: center; margin: 28px 0 20px; }
        .btn { display: inline-block; background: {{ $invitation->tenant->primary_color ?? '#3B82F6' }}; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 8px; }
        .btn:hover { opacity: 0.9; }
        .expiry { text-align: center; font-size: 12px; color: #9ca3af; margin-bottom: 8px; }
        .url-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 14px; font-size: 11px; color: #6b7280; word-break: break-all; margin-top: 20px; }
        .footer { padding: 20px 32px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <h1>You're invited!</h1>
            <p>Seo4ma Workspace Invitation</p>
        </div>
        <div class="body">
            <p>
                <strong>{{ $invitation->inviter->name }}</strong> has invited you to join their workspace on
                <strong>Seo4ma</strong> as a team member.
            </p>

            <div class="workspace-box">
                <div class="ws-name">
                    {{ $invitation->tenant->agency_name ?? $invitation->tenant->name }}
                </div>
                <div class="ws-meta">
                    Role: <span class="badge">{{ $invitation->role }}</span>
                    &nbsp;&bull;&nbsp;
                    Invited to: {{ $invitation->email }}
                </div>
            </div>

            <p>Click the button below to accept this invitation and join the workspace. This link expires in 7 days.</p>

            <div class="btn-wrap">
                <a href="{{ $acceptUrl }}" class="btn">Accept Invitation →</a>
            </div>

            <div class="expiry">
                Expires {{ $invitation->expires_at->format('d M Y, H:i') }} UTC
            </div>

            <div class="url-box">
                If the button doesn't work, copy this link into your browser:<br>
                {{ $acceptUrl }}
            </div>
        </div>
        <div class="footer">
            You received this because someone invited {{ $invitation->email }} to a Seo4ma workspace.
            If this wasn't you, you can safely ignore this email.
            &bull; Seo4ma / Seo4ma
        </div>
    </div>
</div>
</body>
</html>
