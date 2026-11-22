export type DayStatus = 'locked' | 'available' | 'solved';

export type CalendarDayGift = {
    title: string | null;
    message: string | null;
    image_url: string | null;
    link_url: string | null;
    link_label: string | null;
};

export type CalendarDayItem = {
    id: number;
    day: number;
    status: DayStatus;
    available_at: string;
    challenge_type: 'riddle' | 'qcm' | 'game';
    challenge_question: string | null;
    qcm_options: string[] | null;
    game_title: string | null;
    game_instructions: string | null;
    game_symbols: string[] | null;
    has_gift: boolean;
    is_today: boolean;
    gift: CalendarDayGift | null;
};

export type CalendarDayEditSummary = {
    id: number;
    day: number;
    available_at: string;
    challenge_type: 'riddle' | 'qcm' | 'game';
    challenge_summary: string;
    gift_title: string | null;
    gift_image_url: string | null;
};

export type CalendarDayEditItem = {
    id: number;
    day: number;
    available_at: string;
    challenge_type: 'riddle' | 'qcm' | 'game';
    riddle_question: string;
    riddle_answers: string;
    qcm_question: string;
    qcm_options: string[];
    qcm_correct_index: number;
    game_title: string;
    game_instructions: string;
    game_symbols: string;
    gift_title: string;
    gift_message: string;
    gift_link_url: string;
    gift_link_label: string;
    gift_image_url: string | null;
};
