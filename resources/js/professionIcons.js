import { Scissors, Palette, Sparkles, Heart, Hand, User } from 'lucide-vue-next';

const professionIconsMap = {
    'حلاق': Scissors,
    'حلاقة': Scissors,
    'قص': Scissors,
    'مكياج': Palette,
    'تجميل': Sparkles,
    'وشم': Heart,
    'أظافر': Hand,
    'مساج': Hand,
    'تدليك': Hand,
};

export function getProfessionIcon(title) {
    if (!title) return User;

    const normalized = title.trim();

    for (const key in professionIconsMap) {
        if (normalized.includes(key)) {
            return professionIconsMap[key];
        }
    }

    return User;
}