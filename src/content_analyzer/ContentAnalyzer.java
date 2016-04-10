package content_analyzer;

import movies_component.MovieStorager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

/**
 * Created by Sofia on 4/8/2016.
 */
public class ContentAnalyzer {
    private static MovieStorager storagerSQL;

    /**
     * Gets all the movie IDs from the Database
     * @return A list of all the IDs
     * @throws SQLException In case the SQL query fails
     */
    private static ArrayList<String> getMovieIDs() throws SQLException {
        ArrayList<String> ids = new ArrayList<>();

        // The mysql insert statement
        String query = " SELECT id FROM `all_movies` ";

        // Execute the PreparedStatement
        ResultSet rs = storagerSQL.selectQuery(query);
        while (rs.next()) {
            ids.add(rs.getString("id"));
        }
        return ids;
    }

    /**
     * Populates the semantics_plot column of the Database
     * @throws SQLException In case an SQL query fails
     */
    private static void insertSemantics() throws SQLException {
        ArrayList<String> ids = getMovieIDs(); // The IDs of all the movies
        for(String id : ids) { // for each film
            // Fetch the extended plot
            String query = "SELECT `extended_plot` FROM `all_movies` WHERE `id`=" + id;
            ResultSet rs = storagerSQL.selectQuery(query);
            // "Clear" the extended plot
            String extendedPlot = rs.getString("extended_plot");
            //extendedPlot = clearText(extendedPlot);
            // Find the semantics plot
            String semantics = findSemantics(extendedPlot);
            // Insert the semantics plot in the Database
            storagerSQL.insertSemanticPlot(id, semantics);
        }
    }

    /**
     * Finds all the semantically related words
     * @param extendedPlot The extended plot of the film
     * @return The semantics plot of the film
     */
    private static String findSemantics(String extendedPlot) {
        // TODO: 4/10/2016 Find the semantically related words
        return "";
    }

    /**
     * "Clears" text from unnecessary punctuation/stop-words/nouns etc.
     * @param text The text to be "cleared"
     * @return The "cleared" text
     */
    private static String clearText(String text) {
        // TODO: 4/10/2016 Perform the text cleaning
        return "";
    }

    /**
     * Main funtion that performs the content analysis
     * @param args
     * @throws SQLException
     */
    public static void main(String[] args) throws SQLException {
        storagerSQL = new MovieStorager();
        insertSemantics();
        if(!storagerSQL.closeConnection()) {
            System.out.println("Failed to close the connection!");
        }
    }
}
