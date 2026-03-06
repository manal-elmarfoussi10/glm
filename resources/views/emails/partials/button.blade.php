<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;">
    <tr>
        <td style="border-radius: 8px; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
            <a href="{{ $url }}" target="_blank" rel="noopener" style="display: inline-block; padding: 14px 28px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 15px; font-weight: 600; color: #ffffff !important; text-decoration: none;">{{ $slot ?? $text ?? 'Voir' }}</a>
        </td>
    </tr>
</table>
