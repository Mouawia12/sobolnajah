<?php

namespace App\Actions\Publication;

use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Publication;
use App\Services\OpenAiTranslationService;

class CreatePublicationAction
{
    public function __construct(
        private OpenAiTranslationService $translator,
        private PublicationImageManager $imageManager
    ) {
    }

    public function execute(array $data, ?int $currentSchoolId): Publication
    {
        if ($currentSchoolId && (int) $data['school_id2'] !== (int) $currentSchoolId) {
            abort(403, 'غير مصرح لك بإضافة منشورات لمدرسة مختلفة.');
        }

        $translations = $this->translator->translatePublicationContent(
            (string) $data['titlear'],
            (string) $data['bodyar']
        );

        $publication = new Publication();
        $publication->school_id = (int) $data['school_id2'];
        $publication->grade_id = (int) $data['grade_id2'];
        $publication->agenda_id = (int) $data['agenda_id'];
        $publication->title = $translations['title'];
        $publication->body = $translations['body'];
        $publication->like = rand(90, 999);
        $publication->save();

        $images = $this->imageManager->storeMany((array) ($data['img_url'] ?? []));

        $gallery = new Gallery();
        $gallery->publication_id = $publication->id;
        $gallery->agenda_id = (int) $data['agenda_id'];
        $gallery->grade_id = (int) $data['grade_id2'];
        $gallery->img_url = json_encode($images);
        $gallery->save();

        return $publication;
    }
}

