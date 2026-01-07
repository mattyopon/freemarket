<?php

namespace App\Constants;

class ValidationConstants
{
    // 文字数制限
    const MAX_NAME_LENGTH = 255;
    const MAX_EMAIL_LENGTH = 255;
    const MAX_POSTAL_CODE_LENGTH = 10;
    const MAX_ADDRESS_LENGTH = 255;
    const MAX_BUILDING_NAME_LENGTH = 255;
    const MAX_DESCRIPTION_LENGTH = 255;
    const MAX_COMMENT_LENGTH = 255;

    // パスワード制限
    const MIN_PASSWORD_LENGTH = 8;

    // ファイルサイズ制限（KB）
    const MAX_IMAGE_SIZE_KB = 2048;
}

