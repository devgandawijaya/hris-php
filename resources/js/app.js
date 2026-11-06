import Alpine from 'alpinejs';
import { 
    createIcons, 
    LayoutDashboard, 
    Users, 
    UserPlus, 
    UserCheck, 
    Building2, 
    Briefcase, 
    Clock, 
    Calendar, 
    CalendarOff, 
    Banknote, 
    Receipt, 
    Calculator, 
    ShieldCheck, 
    Percent, 
    Globe, 
    Sliders, 
    HelpCircle, 
    Settings, 
    SlidersHorizontal, 
    LogOut, 
    Menu, 
    X, 
    ChevronRight, 
    ChevronDown, 
    CheckCircle2, 
    AlertCircle, 
    Search, 
    Filter, 
    Download, 
    Plus, 
    ArrowRight,
    MapPin,
    Eye,
    TrendingUp
} from 'lucide';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Expose Lucide icons renderer
window.renderIcons = () => {
    createIcons({
        icons: {
            LayoutDashboard,
            Users,
            UserPlus,
            UserCheck,
            Building2,
            Briefcase,
            Clock,
            Calendar,
            CalendarOff,
            Banknote,
            Receipt,
            Calculator,
            ShieldCheck,
            Percent,
            Globe,
            Sliders,
            HelpCircle,
            Settings,
            SlidersHorizontal,
            LogOut,
            Menu,
            X,
            ChevronRight,
            ChevronDown,
            CheckCircle2,
            AlertCircle,
            Search,
            Filter,
            Download,
            Plus,
            ArrowRight,
            MapPin,
            Eye,
            TrendingUp
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.renderIcons();
});

Alpine.start();

