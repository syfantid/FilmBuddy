package movies;

import jwiki.core.NS;
import jwiki.core.Wiki;
import jwiki.dwrap.ImageInfo;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Class to collect all movies and their information from Wikipedia
 * Created by Sofia on 3/31/2016.
 */
public class MovieCollector {

    /**
     * Gets the extended plot of a Wikipedia film page
     * @param page The text of the Wikipedia film page
     * @return The extended plot of the film
     */
    private static String getExtendedPlot(String page) {
        String plot = "";
        String keyword;
        // Plot is always between the Plot and the Cast section
        int index1;
        if(page.contains("==Plot==")) {
            index1 = page.indexOf("==Plot=="); // The index of the Plot title in the page
            keyword = "==Plot==";
        } else if(page.contains("==Synopsis==")) {
            index1 = page.indexOf("==Synopsis=="); // The index of the Plot title in the page
            keyword = "==Synopsis==";
        } else {
            return plot;
        }
        int index2; // The index of the Cast title in the page

        if(page.contains("==Cast==")) { // Cast title is "Cast"
            index2 = page.indexOf("==Cast==");
            if(index1 < index2) {
                plot = page.substring(index1 + keyword.length(),
                        index2);
            }
        } else if(page.contains("==Principal cast==")) { // Cast title is "Principal Cast"
            index2 = page.indexOf("==Principal cast==");
            if(index1 < index2) {
                plot = page.substring(index1 + keyword.length(),
                        index2);
            }
        } else if(page.contains("==Main cast==")){ // Cast title is "Main Cast"
            index2 = page.indexOf("==Main cast==");
            if(index1 < index2) {
                plot = page.substring(index1 + keyword.length(),
                        index2);
            }
        }
        return plot.replaceAll("[\\[\\]]","");
    }

    /**
     * Given a Wikipedia Category name it strips the year
     * @param category The Wikipedia Category name; example format "Category:2000 films'
     * @return The production year of the films in the given category
     */
    private static String getYear(String category) {
        return category.substring(category.indexOf("Category:") + "Category:".length(),
                category.indexOf(" films"));
    }

    /**
     * Gets the director of a film
     * @param page The film's Wikipedia page text
     * @return The name of the director
     */
    private static String getDirector(String page) {
        Matcher m = Pattern.compile("director\\s*=\\s*([(\\w\\s\\u00E0-\\u00FC).\\[\\]]*)").matcher(page);
        if(m.find()) {
            return m.group(1).replaceAll("[\\[\\]]","");
        }
        return "";
    }

    /**
     * Gets the main actors and actresses of a film
     * @param page The film's Wikipedia page text
     * @return The name of the director
     */
    private static String getStars(String page) {
        String stars = "";
        Matcher m = Pattern
                .compile("(?i)starring\\s*=\\s*((\\{\\{Plainlist\\| \\* )?(\\[\\[[\\w\\s\\u00E0-\\u00FC|]*\\]\\]" +
                        "(<br>| \\* |(\\s*<br\\s*/>\\s*))?)*)")
                .matcher(page);
        if(m.find()) {
            stars = m.group(1);
            stars = stars.replaceAll("(?i)<br>| \\* |(\\s*<br\\s*/>\\s*)",", ").replaceAll("[\\[\\]]","")
                    .replace("{{Plainlist|, ",""); //Different format of Info-Box
        }
        return stars;
    }

    /**
     * Gets the synopsis of a film
     * @param page The film's Wikipedia page text
     * @return The synopsis of the film
     */
    private static String getSynopsis(String page) {
        String synopsis = "";
        // From the end of Info-box to the start of a new section/heading
        page = page.replaceAll("<ref>([\\w\\s\\u00E0-\\u00FC\\[\\]{}()|:?\\-=%&;'\"/.,]*)</ref>",""); // Remove all links
        Matcher m = Pattern.compile("}} ([\\w\\d\\s\\u00E0-\\u00FC.,\\[\\]\\/\\|#!%<>?’&;:{}\\-_`'\"~()]*?) ={2,}")
                .matcher(page);
        if(m.find()) {
            synopsis = m.group(1);
            synopsis = synopsis.replaceAll("'{2,}","\"").replaceAll("\\[\\[[\\w\\s()]*\\|","")
                    .replaceAll("[\\[\\]]","");
        }
        return synopsis;

    }

    /**
     * Gets the IMDb URI of a film
     * @param page The film's Wikipedia page text
     * @return The IMDb URI
     */
    private static String getIMDbLink(String page) {
        String link = "";
        String code;
        Matcher m = Pattern.compile("(?i)\\{\\{IMDb title\\|(id\\s*=)?\\s*(\\d*\\w*)").matcher(page);
        if(m.find()) {
            code = m.group(2).replaceAll("\\s+","");
            link = "http://www.imdb.com/title/tt" + code;
        }
        return link;
    }

    /**
     * Gets the Wikipedia poster's URL of a film
     * @param page The film's Wikipedia page text
     * @param wiki The Wikipedia connection object
     * @return The URL of the Wikipedia poster
     */
    private static String getIconURL(String page, Wiki wiki) {
        // TODO: 4/3/2016 ImageInfo Class bug probably
        String imageName;
        String iconURL = "";
        Matcher m = Pattern.compile("(?i)image\\s*=\\s*([\\w\\d\\s.]*)").matcher(page); // Get the image file name
        if(m.find()) {
            imageName = m.group(1).replaceAll("\\s+","");
            ImageInfo image = wiki.getImageInfo(imageName);
            if(image != null) {
                iconURL = image.url;
            }
        }
        return iconURL;
    }

    /**
     * Gets the Wikipedia categories of a film
     * @param page The film's Wikipedia page text
     * @return A list of all the related categories
     */
    private static String getCategories(String page) {
        StringBuilder categories = new StringBuilder();
        Matcher m = Pattern.compile("\\[\\[Category:\\s*([\\w\\d\\s\\u00E0-\\u00FC-]*)\\]\\]*").matcher(page);
        while(m.find()) {
            categories.append(m.group(1)).append(", ");
        }
        categories.deleteCharAt(categories.length()-2);
        return categories.toString();
    }

    public static void main(String[] args) throws Throwable
    {
        Wiki wiki = new Wiki("syfantid", "sofia24041994", "en.wikipedia.org"); // Login to Wikipedia

        // THIS IS A TESTING PART
        /*String category = "Category:2000 films";
        String year = getYear(category); // 1. The year for all the movies in the specific category
        ArrayList<String> films = wiki.getCategoryMembers(category,NS.MAIN); // Get all the articles in this category

        String titleTest = films.get(10); // Film title
        String pageTestFormatted = wiki.getPageText(titleTest); // The text of the article
        String pageTest = pageTestFormatted.replaceAll("\\s+", " ");
        String extendedPlotTest = getExtendedPlot(pageTest); // The extended plot of the film
        if(!extendedPlotTest.isEmpty()) {
            System.out.println("Page for film: " + titleTest);
            System.out.println(pageTestFormatted);
            System.out.println(pageTest);
            System.out.println("STARS");
            System.out.println(getStars(pageTest));
            System.out.println("DIRECTOR");
            System.out.println(getDirector(pageTest));
            System.out.println("SYNOPSIS");
            System.out.println(getSynopsis(pageTest));
            System.out.println("LINK");
            System.out.println(getIMDbLink(pageTest));
            System.out.println("IMAGE");
            //String URL = getIconURL(pageTest,wiki);
            System.out.println("CATEGORIES");
            ArrayList<String> categories = getCategories(pageTest);
            for(String s : categories) {
                System.out.print(s + ", ");
            }
            System.out.println("EXTENDED PLOT");
            System.out.println(extendedPlotTest);
        }*/

        int yearNumber = 1900;
        while(yearNumber <= 2016) {
            String category = "Category:" + yearNumber + " films";
            ArrayList<String> films = wiki.getCategoryMembers(category,NS.MAIN); // Get all the articles in this category

            Iterator<String> it = films.iterator();
            while (it.hasNext()) { // Iterate through the year's movies
                String title = it.next();
                if(title.startsWith("List of")) { // Categories include lists of special films, which will be excluded
                    it.remove();
                } else {
                    String pageFormatted = wiki.getPageText(title); // The text of the article formatted
                    String page = pageFormatted.replaceAll("\\s+", " "); // Remove any extra whitespaces
                    String extendedPlot = getExtendedPlot(page); // The extended plot of the film - REQUIRED FIELD
                    if(!extendedPlot.isEmpty()) { // Exclude films with empty Wikipedia pages
                        // Get all the extra information needed to present the film; OPTIONAL FIELDS
                        String categories = getCategories(page);
                        String synopsis = getSynopsis(page);
                        String iconURL = getIconURL(page, wiki);
                        String cast = getStars(page);
                        String director = getDirector(page);
                        String imdbURL = getIMDbLink(page);
                        Movie m = new Movie(title, yearNumber, categories, synopsis, iconURL, cast, director,
                                imdbURL, extendedPlot); // Create Movie object
                        MovieStorager.InsertMovietoDB(m); // Insert movie to DB
                    }
                }
            }
            yearNumber++; // Parse next year's movies
        }
    }
}
