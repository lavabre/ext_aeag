// ============================================================================
//
// Copyright (C) 2006-2010 Talend Inc. - www.talend.com
//
// This source code is available under agreement available at
// %InstallDIR%\features\org.talend.rcp.branding.%PRODUCTNAME%\%PRODUCTNAME%license.txt
//
// You should have received a copy of the agreement
// along with this program; if not, write to Talend SA
// 9 rue Pages 92150 Suresnes, France
//   
// ============================================================================
package routines.system;

import java.util.Locale;

public class FastDateParser {

    private static FastDateParser instance;

    public static FastDateParser getInstance() {
        if (instance == null) {
            instance = new FastDateParser();
        }
        return instance;
    }

    private FastDateParser() {
        super();
    }
    
    private static ThreadLocal<java.util.HashMap<DateFormatKey, java.text.DateFormat>> localCache = new ThreadLocal<java.util.HashMap<DateFormatKey, java.text.DateFormat>>() {

		@Override
		protected java.util.HashMap<DateFormatKey, java.text.DateFormat> initialValue() {
			return new java.util.HashMap<DateFormatKey, java.text.DateFormat>();
		}
    	
    };
    
    private static ThreadLocal<DateFormatKey> localDateFormatKey= new ThreadLocal<DateFormatKey>() {

		@Override
		protected DateFormatKey initialValue() {
			// TODO Auto-generated method stub
			return getInstance().new DateFormatKey();
		}
    	
    };

    // Warning : DateFormat objects returned by this method are not thread safe
    public static java.text.DateFormat getInstance(String path) {
        return getInstance(path, null, true);
    }

    public static java.text.DateFormat getInstance(String path, boolean lenient) {
        return getInstance(path, null, lenient);
    }

    public static java.text.DateFormat getInstance(String path, Locale locale) {
        return getInstance(path, locale, true);
    }

    public static java.text.DateFormat getInstance(String path, Locale locale, boolean lenient) {
    	localDateFormatKey.get().path = path;
    	localDateFormatKey.get().locale = locale;
        java.text.DateFormat format = localCache.get().get(localDateFormatKey.get());
        if (format == null) {
            if (path.equals("yyyy-MM-dd")) { //$NON-NLS-1$
                format = new DateParser();
            } else if (path.equals("yyyy-MM-dd HH:mm:ss")) { //$NON-NLS-1$
                format = new DateTimeParser();
            } else {
                if (locale != null) {
                    format = new java.text.SimpleDateFormat(path, locale);
                } else {
                    format = new java.text.SimpleDateFormat(path);
                }
            }
            localCache.get().put(getInstance().new DateFormatKey(path, locale), format);
        }
        if (format.isLenient() != lenient) {
            format.setLenient(lenient);
        }
        return format;
    }

    // Parse and format dates with yyyy-MM-dd format
    private static class DateParser extends java.text.DateFormat {

        private int year, month, day;

        public DateParser() {
            calendar = java.util.Calendar.getInstance();
        }

        @Override
        public StringBuffer format(java.util.Date date, StringBuffer toAppendTo, java.text.FieldPosition fieldPosition) {
            calendar.setTime(date);

            // Year
            toAppendTo.append(calendar.get(java.util.Calendar.YEAR));
            while (toAppendTo.length() < 4)
                toAppendTo.insert(0, "0"); //$NON-NLS-1$
            toAppendTo.append("-"); //$NON-NLS-1$

            // Month
            month = calendar.get(java.util.Calendar.MONTH) + 1;
            if (month < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(month);
            toAppendTo.append("-"); //$NON-NLS-1$

            // Day
            day = calendar.get(java.util.Calendar.DAY_OF_MONTH);
            if (day < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(day);

            return toAppendTo;
        }

        @Override
        public java.util.Date parse(String source, java.text.ParsePosition pos) {
            int index = 0;
            try {
                year = Integer.parseInt(source.substring(0, 4));
                index = 5;
                month = Integer.parseInt(source.substring(5, 7)) - 1;
                index = 8;
                day = Integer.parseInt(source.substring(8, 10));

                pos.setIndex(source.length());

                calendar.clear();
                calendar.set(year, month, day);
                return calendar.getTime();
            } catch (Exception e) {
                pos.setErrorIndex(index);
                e.printStackTrace();
            }
            return null;
        }
    }

    // Parse dates with yyyy-MM-dd HH:mm:ss format
    private static class DateTimeParser extends java.text.DateFormat {

        private int year, month, day, hour, minute, second;

        public DateTimeParser() {
            calendar = java.util.Calendar.getInstance();
        }

        @Override
        public StringBuffer format(java.util.Date date, StringBuffer toAppendTo, java.text.FieldPosition fieldPosition) {
            calendar.setTime(date);

            // Year
            toAppendTo.append(calendar.get(java.util.Calendar.YEAR));
            while (toAppendTo.length() < 4)
                toAppendTo.insert(0, "0"); //$NON-NLS-1$
            toAppendTo.append("-"); //$NON-NLS-1$

            // Month
            month = calendar.get(java.util.Calendar.MONTH) + 1;
            if (month < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(month);
            toAppendTo.append("-"); //$NON-NLS-1$

            // Day
            day = calendar.get(java.util.Calendar.DAY_OF_MONTH);
            if (day < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(day);
            toAppendTo.append(" "); //$NON-NLS-1$

            // Hour
            hour = calendar.get(java.util.Calendar.HOUR_OF_DAY);
            if (hour < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(hour);
            toAppendTo.append(":"); //$NON-NLS-1$

            // Minute
            minute = calendar.get(java.util.Calendar.MINUTE);
            if (minute < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(minute);
            toAppendTo.append(":"); //$NON-NLS-1$

            // Second
            second = calendar.get(java.util.Calendar.SECOND);
            if (second < 10)
                toAppendTo.append("0"); //$NON-NLS-1$
            toAppendTo.append(second);

            return toAppendTo;
        }

        @Override
        public java.util.Date parse(String source, java.text.ParsePosition pos) {
            int index = 0;
            try {
                year = Integer.parseInt(source.substring(0, 4));
                index = 5;
                month = Integer.parseInt(source.substring(5, 7)) - 1;
                index = 8;
                day = Integer.parseInt(source.substring(8, 10));
                index = 11;
                hour = Integer.parseInt(source.substring(11, 13));
                index = 14;
                minute = Integer.parseInt(source.substring(14, 16));
                index = 17;
                second = Integer.parseInt(source.substring(17, 19));

                pos.setIndex(source.length());

                calendar.clear();
                calendar.set(year, month, day, hour, minute, second);
                return calendar.getTime();
            } catch (Exception e) {
                pos.setErrorIndex(index);
                e.printStackTrace();
            }
            return null;
        }
    }

    private class DateFormatKey {

        private String path;

        private Locale locale;

        public DateFormatKey() {
        }

        public DateFormatKey(String path, Locale locale) {
            this.path = path;
            this.locale = locale;
        }

        /*
         * (non-Javadoc)
         * 
         * @see java.lang.Object#hashCode()
         */
        @Override
        public int hashCode() {
            final int prime = 31;
            int result = 1;
            result = prime * result + ((this.locale == null) ? 0 : this.locale.hashCode());
            result = prime * result + ((this.path == null) ? 0 : this.path.hashCode());
            return result;
        }

        /*
         * (non-Javadoc)
         * 
         * @see java.lang.Object#equals(java.lang.Object)
         */
        @Override
        public boolean equals(Object obj) {
            if (this == obj)
                return true;
            if (obj == null)
                return false;
            if (getClass() != obj.getClass())
                return false;
            final DateFormatKey other = (DateFormatKey) obj;
            if (this.locale == null) {
                if (other.locale != null)
                    return false;
            } else if (!this.locale.equals(other.locale))
                return false;
            if (this.path == null) {
                if (other.path != null)
                    return false;
            } else if (!this.path.equals(other.path))
                return false;
            return true;
        }

    }

}
