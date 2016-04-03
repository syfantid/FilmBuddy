package movies;

import java.util.ArrayList;

/**
 * Created by Sofia on 4/3/2016.
 */
public class Movie {
    private String title;
    private int year;
    private String categories;
    private String synopsis;
    private String iconURL;
    private String cast;
    private String director;
    private String imdbURL;
    private String extendedPlot;

    public Movie(String title, int year, String categories, String synopsis, String iconURL, String cast,
                 String director, String imdbURL, String extendedPlot) {
        this.title = title;
        this.year = year;
        this.categories = categories;
        this.synopsis = synopsis;
        this.iconURL = iconURL;
        this.cast = cast;
        this.director = director;
        this.imdbURL = imdbURL;
        this.extendedPlot = extendedPlot;
    }

    public String getTitle() {

        return title;
    }

    public int getYear() {
        return year;
    }

    public String getCategories() {
        return categories;
    }

    public String getSynopsis() {
        return synopsis;
    }

    public String getIconURL() {
        return iconURL;
    }

    public String getCast() {
        return cast;
    }

    public String getDirector() {
        return director;
    }

    public String getImdbURL() {
        return imdbURL;
    }

    public String getExtendedPlot() {
        return extendedPlot;
    }
}
