<?php

declare(strict_types=1);

namespace Forumify\Core\Compliance\Disclosure;

enum LegalBasis: string
{
    case Consent = 'consent';
    case Contract = 'contract';
    case LegalObligation = 'legal_obligation';
    case VitalInterests = 'vital_interests';
    case PublicTask = 'public_task';
    case LegitimateInterests = 'legitimate_interests';
}
